<?php
require_once 'db_connection.php';
require_once 'calculate_income.php';
require_once 'calculate_food_consumption.php';
require_once 'calculate_power_consumption.php';
require_once 'calculate_consumer_goods_consumption.php';
require_once 'calculate_population_growth.php';
require_once 'calculate_points.php';
require_once 'calculate_tier.php';

function performHourlyUpdates() {
    global $conn;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Fetch all users, their population, and commodities
        $stmt = $conn->prepare("
            SELECT u.id, u.population, c.food, c.power, c.money, c.consumer_goods, l.urban_areas
            FROM users u 
            JOIN commodities c ON u.id = c.id
            JOIN land l ON u.id = l.id
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        while ($user = $result->fetch_assoc()) {
            $user_id = $user['id'];
            
            // Calculate income
            $income_result = calculateIncome($user);

            if ($income_result['success']) {
                // Update user's money
                $new_money = $income_result['new_money'];
                $update_stmt = $conn->prepare("UPDATE commodities SET money = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $new_money, $user_id);
                $update_stmt->execute();
            }

            // Calculate food consumption
            $food_consumption_result = calculateFoodConsumption($user);

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

            log_message("User ID {$user_id}: " . $consumer_goods_consumption_result['consumption']);
            log_message($consumer_goods_consumption_result['message']);

            // Update user's consumer goods
            $new_consumer_goods = $consumer_goods_consumption_result['new_consumer_goods'];
            $update_stmt = $conn->prepare("UPDATE commodities SET consumer_goods = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_consumer_goods, $user_id);
            $update_stmt->execute();

            // Calculate population growth
            $population_growth_result = calculatePopulationGrowth($user);

            // Update user's population
            $new_population = $population_growth_result['new_population'];
            $update_stmt = $conn->prepare("UPDATE users SET population = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_population, $user_id);
            $update_stmt->execute();

            // Calculate and update user's tier
            $new_tier = calculateTier($new_population);
            $update_stmt = $conn->prepare("UPDATE users SET tier = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_tier, $user_id);
            $update_stmt->execute();
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        log_message("Error during hourly updates: " . $e->getMessage());
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
