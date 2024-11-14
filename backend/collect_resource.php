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
$amount = intval($_POST['amount']);

try {
    // Start transaction
    $pdo->beginTransaction();

    // Fetch factory data
    $stmt = $pdo->prepare("SELECT `$factory_type` FROM factories WHERE id = ?");
    $stmt->execute([$user_id]);
    $factory_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch production capacity
    $stmt = $pdo->prepare("SELECT `$factory_type` AS capacity FROM production_capacity WHERE id = ?");
    $stmt->execute([$user_id]);
    $capacity_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($amount > $capacity_data['capacity']) {
        throw new Exception("Not enough production capacity");
    }

    // Fetch user's commodities with row lock
    $stmt = $pdo->prepare("SELECT * FROM commodities WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $commodities = $stmt->fetch(PDO::FETCH_ASSOC);

    $factory_config = $FACTORY_CONFIG[$factory_type];
    $inputs = $factory_config['input'];
    $outputs = $factory_config['output'];

    // Calculate and store hourly rates first
    $hourly_inputs = [];
    foreach ($inputs as $input) {
        // Calculate base hourly rate per factory
        $hourly_rate = $input['amount'] * $factory_data[$factory_type];
        $hourly_inputs[] = [
            'resource' => $input['resource'],
            'hourly_amount' => $hourly_rate,
            'total_amount' => $hourly_rate * $amount
        ];
    }

    // Check if user has enough resources for hourly production
    foreach ($hourly_inputs as $input) {
        if ($commodities[$input['resource']] < $input['hourly_amount']) {
            throw new Exception("Not enough {$input['resource']}");
        }
    }

    // Check if user has enough total resources
    foreach ($hourly_inputs as $input) {
        if ($commodities[$input['resource']] < $input['total_amount']) {
            throw new Exception("Not enough total {$input['resource']} to collect");
        }
    }

    // Calculate outputs
    $hourly_outputs = [];
    foreach ($outputs as $output) {
        $hourly_rate = $output['amount'] * $factory_data[$factory_type];
        $hourly_outputs[] = [
            'resource' => $output['resource'],
            'total_amount' => $hourly_rate * $amount
        ];
    }

    // Update commodities
    $update_commodities = "UPDATE commodities SET ";
    $update_parts = [];
    $update_values = [];
    
    // Deduct inputs
    foreach ($hourly_inputs as $input) {
        $update_parts[] = "`{$input['resource']}` = GREATEST(0, `{$input['resource']}` - ?)";
        $update_values[] = $input['total_amount'];
    }
    
    // Add outputs
    foreach ($hourly_outputs as $output) {
        $update_parts[] = "`{$output['resource']}` = `{$output['resource']}` + ?";
        $update_values[] = $output['total_amount'];
    }
    
    $update_commodities .= implode(", ", $update_parts) . " WHERE id = ?";
    $update_values[] = $user_id;

    $stmt = $pdo->prepare($update_commodities);
    $stmt->execute($update_values);

    // Update production capacity
    $stmt = $pdo->prepare("UPDATE production_capacity SET `$factory_type` = `$factory_type` - ? WHERE id = ?");
    $stmt->execute([$amount, $user_id]);

    // Get factory display name from config
    $factory_display_name = $FACTORY_CONFIG[$factory_type]['name'];

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully collected resources from {$factory_display_name}"
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
