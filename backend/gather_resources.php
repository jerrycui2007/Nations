<?php
global $conn;
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
    'herbalist_building' => 'Animal',
    'marine_biologist_building' => 'Marine Animal'
];

if (!isset($building_resource_types[$building_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid building type']);
    exit();
}

$conn->begin_transaction();

try {
    // Get building level
    $stmt = $conn->prepare("SELECT $building_type FROM buildings WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $building_level = $stmt->get_result()->fetch_assoc()[$building_type];

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
    $stmt = $conn->prepare($hidden_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $hidden_resources = $stmt->get_result()->fetch_assoc();

    // Prepare updates for both tables
    $update_parts = [];
    $update_values = [];
    $commodities_update = "UPDATE commodities SET ";
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
    $update_values[] = $user_id;
    $stmt = $conn->prepare($commodities_update);
    $stmt->bind_param(str_repeat("i", count($update_values)), ...$update_values);
    $stmt->execute();

    // Reset hidden resources
    $hidden_update .= implode(", ", $hidden_parts) . " WHERE id = ?";
    $stmt = $conn->prepare($hidden_update);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Successfully gathered resources"
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
