<?php
require_once 'db_connection.php';
require_once 'calculate_income.php';

function performHourlyUpdates() {
    global $conn;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch all users, their population, and commodities
        $stmt = $conn->prepare("
            SELECT u.id, u.population, c.food, c.power, c.money 
            FROM users u 
            JOIN commodities c ON u.id = c.id
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($user = $result->fetch_assoc()) {
            $user_id = $user['id'];
            
            // Calculate income using the new function
            $income_result = calculateIncome($user);

            if ($income_result['success']) {
                // Update user's money
                $update_stmt = $conn->prepare("UPDATE commodities SET money = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $income_result['new_money'], $user_id);
                $update_stmt->execute();
            }

            echo "User ID {$user_id}: " . $income_result['message'] . "\n";
        }

        $conn->commit();
        echo "Hourly updates completed successfully.\n";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error during hourly updates: " . $e->getMessage() . "\n";
    }

    $conn->close();
}

function log_message($message) {
    $log_file = __DIR__ . '/hourly_updates.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_message("Starting hourly updates");
performHourlyUpdates();
log_message("Hourly updates completed");
