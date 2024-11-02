<?php
global $pdo;
session_start();
require_once 'db_connection.php';
require_once 'resource_config.php';
require_once 'calculate_points.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Start transaction
$pdo->beginTransaction();

try {
    // Check if user has enough resources
    $stmt = $pdo->prepare("SELECT u.population, c.money, c.food, c.building_materials, c.consumer_goods 
                          FROM users u 
                          JOIN commodities c ON u.id = c.id 
                          WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $multiplier = max(1, $user_data['population'] / 50000);
    $money_cost = round(5000 * $multiplier);
    $resource_cost = round(1000 * $multiplier);

    if ($user_data['money'] < $money_cost || 
        $user_data['food'] < $resource_cost ||
        $user_data['building_materials'] < $resource_cost || 
        $user_data['consumer_goods'] < $resource_cost) {
        throw new Exception("Not enough resources to expand borders");
    }

    // Check if user has already expanded borders today
    $stmt = $pdo->prepare("SELECT expanded_borders_today FROM land WHERE id = ?");
    $stmt->execute([$user_id]);
    $expanded_today = $stmt->fetch(PDO::FETCH_ASSOC)['expanded_borders_today'];

    if ($expanded_today == 1) {
        throw new Exception("You have already expanded your borders today. Please try again tomorrow.");
    }

    // Calculate new land amount
    $new_land_amount = round($user_data['population'] / 2000);

    // Define eligible land types (without backticks in the array)
    $eligible_types = ['cleared_land', 'forest', 'mountain', 'river', 'lake', 'grassland', 'jungle', 'desert', 'tundra'];

    // Distribute new land randomly
    $new_land = array_fill_keys($eligible_types, 0);
    for ($i = 0; $i < $new_land_amount; $i++) {
        $random_type = $eligible_types[array_rand($eligible_types)];
        $new_land[$random_type]++;
    }

    // Get all natural resources and their weights
    $natural_resources = array_filter($RESOURCE_CONFIG, function($resource) {
        return isset($resource['is_natural_resource']) && $resource['is_natural_resource'] === true;
    });

    // Create weighted array for random selection
    $weighted_resources = [];
    foreach ($natural_resources as $resource_key => $resource_data) {
        // Default to weight of 0 if discovery_weight is not set
        $weight = $resource_data['discovery_weight'] ?? 0;
        for ($i = 0; $i < $weight; $i++) {
            $weighted_resources[] = $resource_key;
        }
    }

    // Add resources for each new piece of land
    $new_resources = [];
    $total_new_land = array_sum($new_land);
    for ($i = 0; $i < $total_new_land * 50; $i++) {
        $random_resource = $weighted_resources[array_rand($weighted_resources)];
        if (!isset($new_resources[$random_resource])) {
            $new_resources[$random_resource] = 0;
        }
        $new_resources[$random_resource]++;
    }

    // Update hidden resources
    if (!empty($new_resources)) {
        $update_query = "INSERT INTO hidden_resources (id, `" . implode("`, `", array_keys($new_resources)) . "`) 
                        VALUES (?" . str_repeat(", ?", count($new_resources)) . ")
                        ON DUPLICATE KEY UPDATE " . 
                        implode(", ", array_map(function($key) {
                            return "`$key` = `$key` + VALUES(`$key`)";
                        }, array_keys($new_resources)));

        $update_values = array_merge([$user_id], array_values($new_resources));
        $stmt = $pdo->prepare($update_query);
        $stmt->execute($update_values);
    }

    // Update user's resources
    $stmt = $pdo->prepare("UPDATE commodities SET 
                          money = money - ?, 
                          food = food - ?, 
                          building_materials = building_materials - ?, 
                          consumer_goods = consumer_goods - ? 
                          WHERE id = ?");
    $stmt->execute([$money_cost, $resource_cost, $resource_cost, $resource_cost, $user_id]);

    // Update user's land
    $update_query = "UPDATE land SET ";
    $update_parts = [];
    $update_values = [];
    foreach ($new_land as $type => $amount) {
        if ($amount > 0) {
            $clean_type = trim($type, "'`");
            $update_parts[] = "`{$clean_type}` = `{$clean_type}` + ?";
            $update_values[] = $amount;
        }
    }
    $update_query .= implode(", ", $update_parts) . " WHERE id = ?";
    $update_values[] = $user_id;

    // Set expanded_borders_today to 1
    $stmt = $pdo->prepare("UPDATE land SET expanded_borders_today = 1 WHERE id = ?");
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare($update_query);
    $stmt->execute($update_values);

    // Recalculate GP after expansion
    $new_gp = calculatePoints($user_id);
    
    // Update user's GP
    $stmt = $pdo->prepare("UPDATE users SET gp = ? WHERE id = ?");
    $stmt->execute([$new_gp, $user_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully expanded borders",
        'newLand' => $new_land,
        'newResources' => $new_resources,
        'newGP' => $new_gp
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
