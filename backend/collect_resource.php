<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$factory_type = $_POST['factory_type'];
$amount = intval($_POST['amount']);

// Start transaction
$conn->begin_transaction();

try {
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
        $inputs[] = ['resource' => 'money', 'amount' => $amount * 7];
        $outputs[] = ['resource' => 'food', 'amount' => $amount];
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

    echo json_encode([
        'success' => true,
        'message' => "Successfully collected resources from $factory_type"
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
