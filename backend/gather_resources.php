<?php
global $pdo;
session_start();
require_once 'db_connection.php';
require_once 'resource_config.php';
require_once 'building_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$building_type = $_POST['building_type'] ?? '';

// Map buildings to resource types
$building_resource_types = [
    'geologist_building' => 'Mined',
    'zoologist_building' => 'Fauna',
    'herbalist_building' => 'Fauna',
    'marine_biologist_building' => 'Marine Animal'
];

if (!isset($building_resource_types[$building_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid building type']);
    exit();
}

$pdo->beginTransaction();

try {
    // Get building level
    $stmt = $pdo->prepare("SELECT $building_type FROM buildings WHERE id = ?");
    $stmt->execute([$user_id]);
    $building_level = $stmt->fetch(PDO::FETCH_ASSOC)[$building_type];

    // Add after getting building level (around line 35)
    $cost = $building_level * 1000;  // Same cost calculation as shown in resources.php

    // Check if user has enough money
    $stmt = $pdo->prepare("SELECT money FROM commodities WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_money = $stmt->fetch(PDO::FETCH_ASSOC)['money'];

    if ($user_money < $cost) {
        throw new Exception("Not enough money to gather resources");
    }

    // Get resources of matching type and tier
    $resource_type = $building_resource_types[$building_type];
    $transferable_resources = array_filter($RESOURCE_CONFIG, function($resource) use ($resource_type, $building_level) {
        return isset($resource['is_natural_resource']) && 
               $resource['is_natural_resource'] === true && 
               $resource['type'] === $resource_type && 
               $resource['tier'] <= $building_level;
    });

    if (empty($transferable_resources)) {
        throw new Exception("No resources available to gather");
    }

    // Get current hidden resources
    $resource_columns = array_keys($transferable_resources);
    $hidden_query = "SELECT `" . implode("`, `", $resource_columns) . "` FROM hidden_resources WHERE id = ?";
    error_log("Hidden resources query: " . $hidden_query);  // Debug log
    $stmt = $pdo->prepare($hidden_query);
    $stmt->execute([$user_id]);
    $hidden_resources = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare updates for both tables
    $update_parts = [];
    $hidden_parts = [];
    $update_values = [$cost];  // Start with cost as first parameter
    $commodities_update = "UPDATE commodities SET money = money - ?, ";
    $hidden_update = "UPDATE hidden_resources SET ";

    foreach ($resource_columns as $resource) {
        if (($hidden_resources[$resource] ?? 0) > 0) {
            $update_parts[] = "`$resource` = `$resource` + ?";
            $hidden_parts[] = "`$resource` = 0";
            $update_values[] = $hidden_resources[$resource];
        }
    }

    if (empty($update_parts)) {
        throw new Exception("No hidden resources to gather");
    }

    // Update commodities
    $commodities_update .= implode(", ", $update_parts) . " WHERE id = ?";
    $update_values[] = $user_id;  // Add user_id for WHERE clause
    $stmt = $pdo->prepare($commodities_update);
    $stmt->execute($update_values);

    // Reset hidden resources (no parameters needed since we're setting to 0)
    $hidden_update .= implode(", ", $hidden_parts) . " WHERE id = ?";
    $stmt = $pdo->prepare($hidden_update);
    $stmt->execute([$user_id]);

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => "Successfully gathered resources"
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
