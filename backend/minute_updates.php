<?php
require_once 'db_connection.php';

function performMinuteUpdates() {
    global $conn;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Decrement minutes_left for all entries in factory_queue
        $stmt = $conn->prepare("UPDATE factory_queue SET minutes_left = minutes_left - 1 WHERE minutes_left > 0");
        $stmt->execute();

        // Fetch completed factories
        $stmt = $conn->prepare("SELECT id, factory_type, queue_position FROM factory_queue WHERE minutes_left <= 0");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];
            $factory_type = $row['factory_type'];
            $queue_position = $row['queue_position'];

            // Add the completed factory to the user's factories
            $stmt = $conn->prepare("UPDATE factories SET $factory_type = $factory_type + 1 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            // Delete the completed factory from the queue
            $stmt = $conn->prepare("DELETE FROM factory_queue WHERE id = ? AND factory_type = ? AND queue_position = ?");
            $stmt->bind_param("isi", $user_id, $factory_type, $queue_position);
            $stmt->execute();

            log_message("Completed construction of $factory_type for user $user_id (queue position: $queue_position)");
        }

        $conn->commit();
        log_message("Minute updates completed successfully");
    } catch (Exception $e) {
        $conn->rollback();
        log_message("Error during minute updates: " . $e->getMessage());
    }

    $conn->close();
}

function log_message($message) {
    $log_file = __DIR__ . '/minute_updates.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_message("Starting minute updates");
performMinuteUpdates();
log_message("Minute updates completed");