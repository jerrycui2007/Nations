<?php
session_start();
require_once 'db_connection.php';
require_once 'factory_config.php';
require_once 'resource_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$factory_type = $_POST['factory_type'];

try {
    // Start transaction
    $pdo->beginTransaction();

    if (!isset($FACTORY_CONFIG[$factory_type])) {
        throw new Exception("Invalid factory type");
    }

    $factory_data = $FACTORY_CONFIG[$factory_type];
    $construction_cost = $factory_data['construction_cost'];
    $land_required = $factory_data['land']['amount'];
    $land_type = $factory_data['land']['type'];

    // Check if user has enough resources
    $resource_check_query = "SELECT Money, `{$land_type}`";
    foreach ($construction_cost as $resource) {
        if ($resource['resource'] !== 'Money') {
            $resource_check_query .= ", `{$resource['resource']}`";
        }
    }
    $resource_check_query .= " FROM commodities c JOIN land l ON c.id = l.id WHERE c.id = ?";

    $stmt = $pdo->prepare($resource_check_query);
    $stmt->execute([$user_id]);
    $user_resources = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user has enough resources
    foreach ($construction_cost as $resource) {
        $resource_name = $resource['resource'];
        $amount_needed = $resource['amount'];
        $user_amount = $user_resources[$resource_name];

        if ($user_amount < $amount_needed) {
            // Get display name from RESOURCE_CONFIG
            $display_name = isset($RESOURCE_CONFIG[$resource_name]['display_name']) 
                ? $RESOURCE_CONFIG[$resource_name]['display_name']
                : ucfirst(str_replace('_', ' ', $resource_name));
                
            throw new Exception("Not enough $display_name to build this factory");
        }
    }

    if ($user_resources[$land_type] < $land_required) {
        $display_name = isset($RESOURCE_CONFIG[$land_type]['display_name'])
            ? $RESOURCE_CONFIG[$land_type]['display_name']
            : ucfirst(str_replace('_', ' ', $land_type));
            
        throw new Exception("Not enough $display_name to build this factory");
    }

    // Update user's resources
    $update_resources_query = "UPDATE commodities c JOIN land l ON c.id = l.id SET ";
    $update_parts = [];
    $update_values = [];
    foreach ($construction_cost as $resource) {
        $update_parts[] = "`{$resource['resource']}` = `{$resource['resource']}` - ?";
        $update_values[] = $resource['amount'];
    }
    $update_parts[] = "`{$land_type}` = `{$land_type}` - ?";
    $update_values[] = $land_required;
    $update_parts[] = "used_land = used_land + ?";
    $update_values[] = $land_required;
    
    $update_resources_query .= implode(", ", $update_parts) . " WHERE c.id = ?";
    $update_values[] = $user_id;

    $stmt = $pdo->prepare($update_resources_query);
    $stmt->execute($update_values);

    // Add to construction queue
    $construction_time = $factory_data['construction_time'];
    $stmt = $pdo->prepare("INSERT INTO factory_queue (id, factory_type, minutes_left) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $factory_type, $construction_time]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully started construction of a new {$factory_data['name']}"
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
