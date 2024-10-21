<?php
require_once 'db_connection.php';
require_once 'calculate_income.php';
require_once 'calculate_food_consumption.php';

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
            
            // Calculate income
            $income_result = calculateIncome($user);

            // Calculate food consumption
            $food_consumption_result = calculateFoodConsumption($user);

            if ($income_result['success']) {
                // Update user's money
                $new_money = $income_result['new_money'];
                $update_stmt = $conn->prepare("UPDATE commodities SET money = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $new_money, $user_id);
                $update_stmt->execute();
            }

            // Update user's food (new_food is already guaranteed to be non-negative)
            $new_food = $food_consumption_result['new_food'];
            $update_stmt = $conn->prepare("UPDATE commodities SET food = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_food, $user_id);
            $update_stmt->execute();

            // Calculate power consumption
            $power_consumption_result = calculatePowerConsumption($user);

            // Update user's power
            $new_power = $power_consumption_result['new_power'];
            $update_stmt = $conn->prepare("UPDATE commodities SET power = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_power, $user_id);
            $update_stmt->execute();

            // Calculate consumer goods consumption
            $consumer_goods_consumption_result = calculateConsumerGoodsConsumption($user);

            // Update user's consumer goods
            $new_consumer_goods = $consumer_goods_consumption_result['new_consumer_goods'];
            $update_stmt = $conn->prepare("UPDATE commodities SET consumer_goods = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_consumer_goods, $user_id);
            $update_stmt->execute();

echo "User ID {$user_id}: " . $income_result['message'] . " " . $food_consumption_result['message'] . " " ;

            echo "User ID {$user_id}: " . $income_result['message'] . " " . $food_consumption_result['message'] . " " . $power_consumption_result['message'] . "\n";

            echo "User ID {$user_id}: " . $income_result['message'] . " " . $food_consumption_result['message'] . "\n";
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
