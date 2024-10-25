<?php
session_start();
require_once 'db_connection.php';
require_once 'factory_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$factory_type = $_POST['factory_type'];

// Start transaction
$conn->begin_transaction();

try {
    if (!isset($FACTORY_CONFIG[$factory_type])) {
        throw new Exception("Invalid factory type");
    }

    $factory_data = $FACTORY_CONFIG[$factory_type];
    $construction_cost = $factory_data['construction_cost'];
    $land_required = $factory_data['land']['amount'];
    $land_type = $factory_data['land']['type'];

    // Check if user has enough resources
    $resource_check_query = "SELECT Money, {$land_type}";
    $resource_check_params = [];
    foreach ($construction_cost as $resource) {
        if ($resource['resource'] !== 'Money') {
            $resource_check_query .= ", {$resource['resource']}";
        }
    }
    $resource_check_query .= " FROM commodities c JOIN land l ON c.id = l.id WHERE c.id = ?";
    $resource_check_params[] = $user_id;

    $stmt = $conn->prepare($resource_check_query);
    $stmt->bind_param(str_repeat("i", count($resource_check_params)), ...$resource_check_params);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_resources = $result->fetch_assoc();

    // Check if user has enough resources
    foreach ($construction_cost as $resource) {
        if ($user_resources[$resource['resource']] < $resource['amount']) {
            throw new Exception("Not enough {$resource['resource']} to build the factory");
        }
    }

    if ($user_resources[$land_type] < $land_required) {
        throw new Exception("Not enough {$land_type} to build the factory");
    }

    // Update user's resources
    $update_resources_query = "UPDATE commodities c JOIN land l ON c.id = l.id SET ";
    $update_parts = [];
    $update_values = [];
    foreach ($construction_cost as $resource) {
        $update_parts[] = "{$resource['resource']} = {$resource['resource']} - ?";
        $update_values[] = $resource['amount'];
    }
    $update_parts[] = "{$land_type} = {$land_type} - ?";
    $update_values[] = $land_required;
    $update_parts[] = "used_land = used_land + ?";
    $update_values[] = $land_required;
    $update_resources_query .= implode(", ", $update_parts) . " WHERE c.id = ?";
    $update_values[] = $user_id;

    $stmt = $conn->prepare($update_resources_query);
    $stmt->bind_param(str_repeat("i", count($update_values)), ...$update_values);
    $stmt->execute();

    // Add to construction queue
    $construction_time = $factory_data['construction_time'];
    $stmt = $conn->prepare("INSERT INTO factory_queue (id, factory_type, minutes_left) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $factory_type, $construction_time);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully started construction of a new {$factory_data['name']}"
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
