<?php
require_once __DIR__ . '/db_connection.php';
require_once 'calculate_income.php';
require_once 'calculate_food_consumption.php';
require_once 'calculate_power_consumption.php';
require_once 'calculate_consumer_goods_consumption.php';
require_once 'calculate_population_growth.php';
require_once 'calculate_tier.php';
require_once 'gp_functions.php';
require_once 'mission_config.php';

function assignMissions() {
    global $pdo, $MISSION_CONFIG;

    try {
        // Get all users and their current mission count
        $stmt = $pdo->prepare("
            SELECT u.id, COUNT(m.mission_id) as mission_count
            FROM users u
            LEFT JOIN missions m ON u.id = m.user_id
            GROUP BY u.id
            HAVING mission_count < 2
        ");
        $stmt->execute();
        $users_needing_missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users_needing_missions as $user) {
            $missions_to_create = 2 - $user['mission_count'];

            for ($i = 0; $i < $missions_to_create; $i++) {
                // Get user's current missions
                $stmt = $pdo->prepare("
                    SELECT mission_type 
                    FROM missions 
                    WHERE user_id = ?
                ");
                $stmt->execute([$user['id']]);
                $existing_missions = $stmt->fetchAll(PDO::FETCH_COLUMN);

                // Calculate total weight for mission selection
                $total_weight = 0;
                $available_missions = array_diff_key($MISSION_CONFIG, array_flip($existing_missions));

                foreach ($available_missions as $type => $config) {
                    $total_weight += $config['spawn_weight'];
                }

                // Select random mission type based on weights
                $random = rand(1, $total_weight);
                $current_weight = 0;
                $selected_mission_type = '';

                foreach ($available_missions as $type => $config) {
                    $current_weight += $config['spawn_weight'];
                    if ($random <= $current_weight) {
                        $selected_mission_type = $type;
                        break;
                    }
                }

                // Create new mission
                $stmt = $pdo->prepare("
                    INSERT INTO missions (
                        user_id, mission_type, status, 
                        rewards_claimed, battle_id
                    ) VALUES (
                        ?, ?, 'incomplete',
                        FALSE, NULL
                    )
                ");
                $stmt->execute([$user['id'], $selected_mission_type]);
            }
        }

        log_message("Mission assignment completed successfully");
    } catch (Exception $e) {
        log_message("Error during mission assignment: " . $e->getMessage());
        throw $e;
    }
}

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
            $new_tier = calculateTier($population_growth_result['new_population']);
            $update_stmt = $pdo->prepare("UPDATE users SET tier = ? WHERE id = ?");
            $update_stmt->execute([$new_tier, $user_id]);

            // Update production capacity
            updateProductionCapacity($user_id);

            // Calculate and update GP
            try {
                $gp_data = calculateTotalGP($pdo, $user_id);
                log_message("Updated GP for user $user_id: " . json_encode($gp_data));
            } catch (Exception $e) {
                log_message("Error calculating GP for user $user_id: " . $e->getMessage());
            }
        }

        $pdo->commit();
        log_message("Hourly updates completed successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("Error during hourly updates: " . $e->getMessage());
    }

    // After the user loop completes
    try {
        $pdo->beginTransaction();
        
        // Update mobilization states globally
        $stmt = $pdo->prepare("
            UPDATE divisions 
            SET mobilization_state = 'mobilized' 
            WHERE mobilization_state = 'mobilizing'
        ");
        $stmt->execute();
        
        $pdo->commit();
        log_message("Mobilization states updated successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("Error updating mobilization states: " . $e->getMessage());
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
assignMissions();
performHourlyUpdates();
log_message("Hourly updates completed");