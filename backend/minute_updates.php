<?php
require_once 'db_connection.php';
require_once 'gp_functions.php';

function performMinuteUpdates() {
    global $pdo;

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Decrement minutes_left for all entries in factory_queue
        $stmt = $pdo->prepare("UPDATE factory_queue SET minutes_left = minutes_left - 1 WHERE minutes_left > 0");
        $stmt->execute();
        $affected_rows = $stmt->rowCount();
        log_message("Updated factory queue times. Affected rows: " . $affected_rows);

        // Decrement minutes_left for all entries in building_queue
        $stmt = $pdo->prepare("UPDATE building_queue SET minutes_left = minutes_left - 1 WHERE minutes_left > 0");
        $stmt->execute();
        $affected_rows = $stmt->rowCount();
        log_message("Updated building queue times. Affected rows: " . $affected_rows);

        // Fetch completed factories
        $stmt_fetch = $pdo->prepare("SELECT id, factory_type, queue_position FROM factory_queue WHERE minutes_left <= 0");
        $stmt_fetch->execute();
        
        while ($row = $stmt_fetch->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $row['id'];
            $factory_type = $row['factory_type'];
            $queue_position = $row['queue_position'];

            // Add the completed factory to the user's factories
            $stmt_update = $pdo->prepare("UPDATE factories SET `$factory_type` = `$factory_type` + 1 WHERE id = ?");
            $stmt_update->execute([$user_id]);

            // Update GP - modified to use the function instead of class
            $new_gp = calculateTotalGP($pdo, $user_id)['total_gp'];
            $stmt_gp = $pdo->prepare("UPDATE users SET gp = ? WHERE id = ?");
            $stmt_gp->execute([$new_gp, $user_id]);

            // Delete the completed factory from the queue
            $stmt_delete = $pdo->prepare("DELETE FROM factory_queue WHERE id = ? AND factory_type = ? AND queue_position = ?");
            $stmt_delete->execute([$user_id, $factory_type, $queue_position]);

            log_message("Completed construction of $factory_type for user $user_id (queue position: $queue_position)");
        }

        // Update building queue
        $stmt = $pdo->prepare("SELECT id, building_type, level, minutes_left FROM building_queue WHERE minutes_left <= 0");
        $stmt->execute();

        while ($building_upgrade = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $building_upgrade['id'];
            $building_type = $building_upgrade['building_type'];
            $target_level = $building_upgrade['level'];

            // Upgrade is complete
            $stmt_update = $pdo->prepare("UPDATE buildings SET $building_type = ? WHERE id = ?");
            $stmt_update->execute([$target_level, $user_id]);

            // Remove from queue
            $stmt_delete = $pdo->prepare("DELETE FROM building_queue WHERE id = ? AND building_type = ?");
            $stmt_delete->execute([$user_id, $building_type]);

            log_message("Completed upgrade of $building_type to level $target_level for user $user_id");
        }

        $pdo->commit();
        log_message("Minute updates completed successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("Error during minute updates: " . $e->getMessage());
    }
}

function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n"; 
    
    $log_file = __DIR__ . '/minute_updates.log';
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

function main() {
    log_message("Starting minute updates");
    performMinuteUpdates();
    log_message("Minute updates completed");
}

main();
