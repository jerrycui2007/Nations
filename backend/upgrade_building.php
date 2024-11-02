<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("Upgrade building request received: " . print_r($_POST, true));

session_start();
require_once 'db_connection.php';
require_once 'building_config.php';
require_once 'resource_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$building_type = $_POST['building_type'] ?? '';

error_log("Processing upgrade for user_id: $user_id, building_type: $building_type");

if (!isset($BUILDING_CONFIG[$building_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid building type']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get current building level
    $stmt = $pdo->prepare("SELECT $building_type FROM buildings WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        throw new Exception("Could not fetch building data");
    }

    $current_level = $result[$building_type];
    $next_level = $current_level + 1;

    error_log("Current level: $current_level, Next level: $next_level");

    if (!isset($BUILDING_CONFIG[$building_type]['levels'][$next_level])) {
        throw new Exception("Maximum level reached for this building");
    }

    $next_level_data = $BUILDING_CONFIG[$building_type]['levels'][$next_level];

    // Check if an upgrade is already in progress for this building
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM building_queue WHERE id = ? AND building_type = ?");
    $stmt->execute([$user_id, $building_type]);
    $upgrade_in_progress = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if ($upgrade_in_progress) {
        throw new Exception("An upgrade for this building is already in progress.");
    }

    // Check if user has enough resources
    $resource_columns = array_keys($next_level_data['construction_cost']);
    $resource_columns = array_filter($resource_columns, function($resource) {
        return $resource !== 'construction_time';
    });
    $resource_query = "SELECT " . implode(", ", $resource_columns) . " FROM commodities WHERE id = ?";
    $stmt = $pdo->prepare($resource_query);
    $stmt->execute([$user_id]);
    $user_resources = $stmt->fetch(PDO::FETCH_ASSOC);

    foreach ($next_level_data['construction_cost'] as $resource => $amount) {
        if ($resource !== 'construction_time' && $user_resources[$resource] < $amount) {
            throw new Exception("Not enough " . $RESOURCE_CONFIG[$resource]['display_name'] . " to upgrade building");
        }
    }

    // Check if user has enough land
    $stmt = $pdo->prepare("SELECT cleared_land FROM land WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_land = $stmt->fetch(PDO::FETCH_ASSOC);

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

    $stmt = $pdo->prepare($update_query);
    $stmt->execute($update_values);

    // Deduct land
    $stmt = $pdo->prepare("UPDATE land SET cleared_land = cleared_land - ? WHERE id = ?");
    $stmt->execute([$next_level_data['land']['cleared_land'], $user_id]);

    // Add to construction queue
    $construction_time = $next_level_data['construction_cost']['construction_time'];
    $stmt = $pdo->prepare("INSERT INTO building_queue (id, building_type, level, minutes_left) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $building_type, $next_level, $construction_time]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully started upgrade of {$BUILDING_CONFIG[$building_type]['name']} to level $next_level"
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("PDO Error in upgrade_building.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "Database error occurred",
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("General Error in upgrade_building.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
