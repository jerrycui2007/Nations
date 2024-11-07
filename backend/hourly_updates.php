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
    global $pdo;

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Fetch all users, their population, and commodities
        $stmt = $pdo->prepare("
            SELECT u.id, u.population, c.food, c.power, c.money, c.consumer_goods, l.urban_areas
            FROM users u 
            JOIN commodities c ON u.id = c.id
            JOIN land l ON u.id = l.id
        ");
        $stmt->execute();
        
        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $user['id'];
            
            // Calculate income
            $income_result = calculateIncome($user);

            if ($income_result['success']) {
                $update_stmt = $pdo->prepare("UPDATE commodities SET money = ? WHERE id = ?");
                $update_stmt->execute([$income_result['new_money'], $user_id]);
            }

            // Calculate food consumption
            $food_consumption_result = calculateFoodConsumption($user);
            $update_stmt = $pdo->prepare("UPDATE commodities SET food = ? WHERE id = ?");
            $update_stmt->execute([$food_consumption_result['new_food'], $user_id]);

            // Calculate power consumption
            $power_consumption_result = calculatePowerConsumption($user);
            $update_stmt = $pdo->prepare("UPDATE commodities SET power = ? WHERE id = ?");
            $update_stmt->execute([$power_consumption_result['new_power'], $user_id]);

            // Calculate consumer goods consumption
            $consumer_goods_consumption_result = calculateConsumerGoodsConsumption($user);
            $update_stmt = $pdo->prepare("UPDATE commodities SET consumer_goods = ? WHERE id = ?");
            $update_stmt->execute([$consumer_goods_consumption_result['new_consumer_goods'], $user_id]);

            // Calculate population growth
            $population_growth_result = calculatePopulationGrowth($user);
            $update_stmt = $pdo->prepare("UPDATE users SET population = ? WHERE id = ?");
            $update_stmt->execute([$population_growth_result['new_population'], $user_id]);

            // Calculate and update user's tier
            $new_tier = calculateTier($new_population);
            $update_stmt = $pdo->prepare("UPDATE users SET tier = ? WHERE id = ?");
            $update_stmt->execute([$new_tier, $user_id]);

            // New function to update production capacity
            updateProductionCapacity($user_id);

            // Recalculate and update user's points
            calculatePoints($user_id);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("Error during hourly updates: " . $e->getMessage());
    }
}

function updateProductionCapacity($user_id) {
    global $pdo, $FACTORY_CONFIG;

    // Get all factory types from FACTORY_CONFIG
    $factory_types = array_keys($FACTORY_CONFIG);

    // Fetch user's factories
    $stmt = $pdo->prepare("SELECT * FROM factories WHERE id = ?");
    $stmt->execute([$user_id]);
    $factories = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch user's current production capacity
    $stmt = $pdo->prepare("SELECT * FROM production_capacity WHERE id = ?");
    $stmt->execute([$user_id]);
    $capacities = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare update statement
    $update_parts = [];
    foreach ($factory_types as $type) {
        if ($factories[$type] > 0) {
            $update_parts[] = "$type = LEAST($type + 1, 24)";
        }
    }

    if (!empty($update_parts)) {
        $update_sql = "UPDATE production_capacity SET " . implode(", ", $update_parts) . " WHERE id = ?";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([$user_id]);

        log_message("Updated production capacity for user $user_id");
    }
}

function log_message($message) {
    $log_file = __DIR__ . '/hourly_updates.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_message("Starting hourly updates");
performHourlyUpdates();
log_message("Hourly updates completed");
