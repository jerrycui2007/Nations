<?php
ob_start(); // Start output buffering

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connection.php';



try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $factory_type = $_POST['factory_type'];
    $amount = intval($_POST['amount']);
    
    // Start transaction
    $conn->begin_transaction();
    
    // Fetch factory data
    $stmt = $conn->prepare("SELECT $factory_type FROM factories WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $factory_data = $result->fetch_assoc();

    // Fetch production capacity
    $stmt = $conn->prepare("SELECT $factory_type AS capacity FROM production_capacity WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $capacity_data = $result->fetch_assoc();

    if ($amount > $capacity_data['capacity']) {
        throw new Exception("Not enough production capacity");
    }

    // Fetch user's commodities
    $stmt = $conn->prepare("SELECT * FROM commodities WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $commodities = $result->fetch_assoc();

    // Calculate input and output based on factory type
    $inputs = [];
    $outputs = [];
    if ($factory_type === 'farm') {
        $inputs[] = ['resource' => 'Money', 'amount' => $amount * 7 * $factory_data[$factory_type]];
        $outputs[] = ['resource' => 'Food', 'amount' => $amount * $factory_data[$factory_type]];
    }
    elseif ($factory_type === 'windmill') {
        $inputs[] = ['resource' => 'Money', 'amount' => $amount * 2 * $factory_data[$factory_type]];
        $outputs[] = ['resource' => 'Power', 'amount' => $amount * $factory_data[$factory_type]];
    }
    elseif ($factory_type === 'quarry' || $factory_type === 'sandstone_quarry' || $factory_type === 'sawmill') {
        $inputs[] = ['resource' => 'Money', 'amount' => $amount * 7 * $factory_data[$factory_type]];
        $outputs[] = ['resource' => 'Building Materials', 'amount' => $amount * $factory_data[$factory_type]];
    }
    elseif ($factory_type === 'automobile_factory') {
        $inputs[] = ['resource' => 'Money', 'amount' => $amount * 12 * $factory_data[$factory_type]];
        $inputs[] = ['resource' => 'Power', 'amount' => $amount * 10 * $factory_data[$factory_type]];
        $inputs[] = ['resource' => 'Metal', 'amount' => $amount * $factory_data[$factory_type]];
        $outputs[] = ['resource' => 'Consumer Goods', 'amount' => $amount * 6 * $factory_data[$factory_type]];
    }
    // Add more conditions for other factory types here

    // Check if user has enough resources
    foreach ($inputs as $input) {
        if ($commodities[$input['resource']] < $input['amount']) {
            throw new Exception("Not enough {$input['resource']} to collect");
        }
    }

    // Update commodities
    $update_commodities = "UPDATE commodities SET ";
    $update_parts = [];
    $update_values = [];
    foreach ($inputs as $input) {
        $update_parts[] = "{$input['resource']} = {$input['resource']} - ?";
        $update_values[] = $input['amount'];
    }
    foreach ($outputs as $output) {
        $update_parts[] = "{$output['resource']} = {$output['resource']} + ?";
        $update_values[] = $output['amount'];
    }
    $update_commodities .= implode(", ", $update_parts) . " WHERE id = ?";
    $update_values[] = $user_id;

    $stmt = $conn->prepare($update_commodities);
    $stmt->bind_param(str_repeat("i", count($update_values)), ...$update_values);
    $stmt->execute();

    // Update production capacity
    $stmt = $conn->prepare("UPDATE production_capacity SET $factory_type = $factory_type - ? WHERE id = ?");
    $stmt->bind_param("ii", $amount, $user_id);
    $stmt->execute();

    $conn->commit();

    $response = [
        'success' => true,
        'message' => "Successfully collected resources from $factory_type"
    ];
} catch (Exception $e) {
    $conn->rollback();
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ];
}

$conn->close();

// Capture any output that occurred before this point
$output = ob_get_clean();

if (!empty($output)) {
    $response['debug_output'] = $output;
}

header('Content-Type: application/json');
echo json_encode($response);
