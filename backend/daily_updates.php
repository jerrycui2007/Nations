<?php
require_once 'db_connection.php';

function performDailyUpdates() {
    global $pdo;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Reset expanded_borders_today for all users
        $stmt = $pdo->prepare("UPDATE land SET expanded_borders_today = 0");
        $stmt->execute();
        
        $affected_rows = $stmt->rowCount();
        log_message("Reset expanded_borders_today for {$affected_rows} users");

        $pdo->commit();
        log_message("Daily updates completed successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("Error during daily updates: " . $e->getMessage());
    }
}

function log_message($message) {
    $log_file = __DIR__ . '/daily_updates.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_message("Starting daily updates");
performDailyUpdates();
log_message("Daily updates completed");