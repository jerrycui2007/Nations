<?php
global $conn;
session_start();
require_once 'db_connection.php';
require_once 'building_config.php';
require_once 'resource_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$building_type = $_POST['building_type'];

if (!isset($BUILDING_CONFIG[$building_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid building type']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Get current building level
    $stmt = $conn->prepare("SELECT $building_type FROM buildings WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_level = $result->fetch_assoc()[$building_type];
    $next_level = $current_level + 1;

    if (!isset($BUILDING_CONFIG[$building_type]['levels'][$next_level])) {
        throw new Exception("Maximum level reached for this building");
    }

    $next_level_data = $BUILDING_CONFIG[$building_type]['levels'][$next_level];

    // Check if an upgrade is already in progress for this building
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM building_queue WHERE id = ? AND building_type = ?");
    $stmt->bind_param("is", $user_id, $building_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $upgrade_in_progress = $result->fetch_assoc()['count'] > 0;

    if ($upgrade_in_progress) {
        throw new Exception("An upgrade for this building is already in progress.");
    }

    // Check if user has enough resources
    $resource_columns = array_keys($next_level_data['construction_cost']);
    $resource_columns = array_filter($resource_columns, function($resource) {
        return $resource !== 'construction_time';
    });
    $resource_query = "SELECT " . implode(", ", $resource_columns) . " FROM commodities WHERE id = ?";
    $stmt = $conn->prepare($resource_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_resources = $result->fetch_assoc();

    foreach ($next_level_data['construction_cost'] as $resource => $amount) {
        if ($resource !== 'construction_time' && $user_resources[$resource] < $amount) {
            throw new Exception("Not enough " . $RESOURCE_CONFIG[$resource]['display_name'] . " to upgrade building");
        }
    }

    // Check if user has enough land
    $stmt = $conn->prepare("SELECT cleared_land FROM land WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_land = $result->fetch_assoc();

    if ($user_land['cleared_land'] < $next_level_data['land']['cleared_land']) {
        throw new Exception("Not enough cleared land to upgrade building");
    }

    // Deduct resources
    $update_query = "UPDATE commodities SET ";
    $update_parts = [];
    $update_values = [];
    foreach ($next_level_data['construction_cost'] as $resource => $amount) {
        if ($resource !== 'construction_time') {
            $update_parts[] = "$resource = $resource - ?";
            $update_values[] = $amount;
        }
    }
    $update_query .= implode(", ", $update_parts) . " WHERE id = ?";
    $update_values[] = $user_id;

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param(str_repeat("i", count($update_values)), ...$update_values);
    $stmt->execute();

    // Deduct land
    $stmt = $conn->prepare("UPDATE land SET cleared_land = cleared_land - ? WHERE id = ?");
    $stmt->bind_param("ii", $next_level_data['land']['cleared_land'], $user_id);
    $stmt->execute();

    // Add to construction queue
    $construction_time = $next_level_data['construction_cost']['construction_time'];
    $stmt = $conn->prepare("INSERT INTO building_queue (id, building_type, level, minutes_left) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isii", $user_id, $building_type, $next_level, $construction_time);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully started upgrade of {$BUILDING_CONFIG[$building_type]['name']} to level $next_level"
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
