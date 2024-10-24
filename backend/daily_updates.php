<?php
require_once 'db_connection.php';

function performDailyUpdates() {
    global $conn;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Reset expanded_borders_today for all users
        $stmt = $conn->prepare("UPDATE land SET expanded_borders_today = 0");
        $stmt->execute();

        $affected_rows = $stmt->affected_rows;
        log_message("Reset expanded_borders_today for {$affected_rows} users");

        $conn->commit();
        log_message("Daily updates completed successfully");
    } catch (Exception $e) {
        $conn->rollback();
        log_message("Error during daily updates: " . $e->getMessage());
    }

    $conn->close();
}

function log_message($message) {
    $log_file = __DIR__ . '/daily_updates.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_message("Starting daily updates");
performDailyUpdates();
log_message("Daily updates completed");
