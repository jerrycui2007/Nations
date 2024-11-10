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

    // Fetch user's commodities
    $stmt = $pdo->prepare("SELECT * FROM commodities WHERE id = ?");
    $stmt->execute([$user_id]);
    $commodities = $stmt->fetch(PDO::FETCH_ASSOC);

    $factory_config = $FACTORY_CONFIG[$factory_type];
    $inputs = $factory_config['input'];
    $outputs = $factory_config['output'];

    foreach ($inputs as &$input) {
        $input['amount'] *= $amount * $factory_data[$factory_type];
    }
    foreach ($outputs as &$output) {
        $output['amount'] *= $amount * $factory_data[$factory_type];
    }

    // Check if user has enough resources (with factory multiplier)
    $factory_multiplier = $factory_data[$factory_type];
    foreach ($inputs as $input) {
        $total_required = $input['amount'] * $factory_multiplier;
        if ($commodities[$input['resource']] < $total_required) {
            throw new Exception("Not enough {$input['resource']} to collect");
        }
    }

    // Update commodities
    $update_commodities = "UPDATE commodities SET ";
    $update_parts = [];
    $update_values = [];
    foreach ($inputs as $input) {
        $update_parts[] = "`{$input['resource']}` = `{$input['resource']}` - ?";
        $update_values[] = $input['amount'];
    }
    foreach ($outputs as $output) {
        $update_parts[] = "`{$output['resource']}` = `{$output['resource']}` + ?";
        $update_values[] = $output['amount'];
    }
    $update_commodities .= implode(", ", $update_parts) . " WHERE id = ?";
    $update_values[] = $user_id;

    $stmt = $pdo->prepare($update_commodities);
    $stmt->execute($update_values);

    // Update production capacity
    $stmt = $pdo->prepare("UPDATE production_capacity SET `$factory_type` = `$factory_type` - ? WHERE id = ?");
    $stmt->execute([$amount, $user_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully collected resources from $factory_type"
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
