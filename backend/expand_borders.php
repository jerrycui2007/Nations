<?php
global $conn;
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if user has enough resources
    $stmt = $conn->prepare("SELECT u.population, c.money, c.food, c.building_materials, c.consumer_goods 
                            FROM users u 
                            JOIN commodities c ON u.id = c.id 
                            WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

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
    $stmt = $conn->prepare("SELECT expanded_borders_today FROM land WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $expanded_today = $result->fetch_assoc()['expanded_borders_today'];

    if ($expanded_today == 1) {
        throw new Exception("You have already expanded your borders today. Please try again tomorrow.");
    }

    // Calculate new land amount
    $new_land_amount = round($user_data['population'] / 2000);

    // Define eligible land types
    $eligible_types = ['cleared_land', 'forest', 'mountain', 'river', 'lake', 'grassland', 'jungle', 'desert', 'tundra'];

    // Distribute new land randomly
    $new_land = array_fill_keys($eligible_types, 0);
    for ($i = 0; $i < $new_land_amount; $i++) {
        $random_type = $eligible_types[array_rand($eligible_types)];
        $new_land[$random_type]++;
    }

    // Update user's resources
    $stmt = $conn->prepare("UPDATE commodities SET 
                            money = money - ?, 
                            food = food - ?, 
                            building_materials = building_materials - ?, 
                            consumer_goods = consumer_goods - ? 
                            WHERE id = ?");
    $stmt->bind_param("iiiii", $money_cost, $resource_cost, $resource_cost, $resource_cost, $user_id);
    $stmt->execute();

    // Update user's land
    $update_query = "UPDATE land SET ";
    $update_parts = [];
    $update_values = [];
    foreach ($new_land as $type => $amount) {
        if ($amount > 0) {
            $update_parts[] = "$type = $type + ?";
            $update_values[] = $amount;
        }
    }
    $update_query .= implode(", ", $update_parts) . " WHERE id = ?";
    $update_values[] = $user_id;

    // Set expanded_borders_today to 1
    $stmt = $conn->prepare("UPDATE land SET expanded_borders_today = 1 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param(str_repeat("i", count($update_values)), ...$update_values);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully expanded borders",
        'newLand' => $new_land
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();