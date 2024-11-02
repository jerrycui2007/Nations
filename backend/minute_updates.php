<?php
require_once 'db_connection.php';
require_once 'calculate_points.php';

function performMinuteUpdates() {
    global $pdo;

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Decrement minutes_left for all entries in factory_queue
        $stmt = $pdo->prepare("UPDATE factory_queue SET minutes_left = minutes_left - 1 WHERE minutes_left > 0");
        $stmt->execute();

        // Fetch completed factories
        $stmt = $pdo->prepare("SELECT id, factory_type, queue_position FROM factory_queue WHERE minutes_left <= 0");
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $row['id'];
            $factory_type = $row['factory_type'];
            $queue_position = $row['queue_position'];

            // Add the completed factory to the user's factories
            $stmt = $pdo->prepare("UPDATE factories SET $factory_type = $factory_type + 1 WHERE id = ?");
            $stmt->execute([$user_id]);

            calculatePoints($user_id);

            // Delete the completed factory from the queue
            $stmt = $pdo->prepare("DELETE FROM factory_queue WHERE id = ? AND factory_type = ? AND queue_position = ?");
            $stmt->execute([$user_id, $factory_type, $queue_position]);

            log_message("Completed construction of $factory_type for user $user_id (queue position: $queue_position)");
        }

        // Update building queue
        $stmt = $pdo->prepare("SELECT id, building_type, level, minutes_left FROM building_queue");
        $stmt->execute();

        while ($building_upgrade = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $building_upgrade['id'];
            $building_type = $building_upgrade['building_type'];
            $target_level = $building_upgrade['level'];
            $minutes_left = $building_upgrade['minutes_left'] - 1;

            if ($minutes_left <= 0) {
                // Upgrade is complete
                $stmt = $pdo->prepare("UPDATE buildings SET $building_type = ? WHERE id = ?");
                $stmt->execute([$target_level, $user_id]);

                // Remove from queue
                $stmt = $pdo->prepare("DELETE FROM building_queue WHERE id = ? AND building_type = ?");
                $stmt->execute([$user_id, $building_type]);

                log_message("Completed upgrade of $building_type to level $target_level for user $user_id");
            } else {
                // Update remaining time
                $stmt = $pdo->prepare("UPDATE building_queue SET minutes_left = ? WHERE id = ? AND building_type = ?");
                $stmt->execute([$minutes_left, $user_id, $building_type]);
            }
        }

        $pdo->commit();
        log_message("Minute updates completed successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("Error during minute updates: " . $e->getMessage());
    }
}

function log_message($message) {
    $log_file = __DIR__ . '/minute_updates.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_message("Starting minute updates");
performMinuteUpdates();
log_message("Minute updates completed");
