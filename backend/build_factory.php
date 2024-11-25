<?php
session_start();
require_once 'db_connection.php';
require_once 'factory_config.php';
require_once 'resource_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$factory_type = $_POST['factory_type'] ?? '';
$amount = intval($_POST['amount'] ?? 1); // Get the amount, default to 1 if not specified

if (!isset($FACTORY_CONFIG[$factory_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid factory type']);
    exit();
}

// Add helper function at the top
function getResourceDisplayName($resourceKey) {
    global $RESOURCE_CONFIG;
    return isset($RESOURCE_CONFIG[$resourceKey]['display_name']) 
        ? $RESOURCE_CONFIG[$resourceKey]['display_name'] 
        : ucfirst($resourceKey);
}

// Get user's population to calculate tier
$stmt = $pdo->prepare("SELECT population FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_population = $stmt->fetch(PDO::FETCH_ASSOC)['population'];

// Calculate user's tier
require_once 'calculate_tier.php';
$user_tier = calculateTier($user_population);

// Check if user meets tier requirement
$factory_tier = $FACTORY_CONFIG[$factory_type]['tier'];
if ($user_tier < $factory_tier) {
    echo json_encode([
        'success' => false, 
        'message' => "This factory requires Tier {$factory_tier}. Your nation is Tier {$user_tier}."
    ]);
    exit();
}

try {
    $pdo->beginTransaction();

    // Get user's current resources
    $stmt = $pdo->prepare("SELECT * FROM commodities c JOIN land l ON c.id = l.id WHERE c.id = ?");
    $stmt->execute([$user_id]);
    $user_resources = $stmt->fetch(PDO::FETCH_ASSOC);

    $factory_data = $FACTORY_CONFIG[$factory_type];
    
    // Calculate total costs
    $total_land_required = $factory_data['land']['amount'] * $amount;
    $land_type = $factory_data['land']['type'];
    
    // Check if user has enough land
    if ($user_resources[$land_type] < $total_land_required) {
        $land_display_name = getResourceDisplayName($land_type);
        throw new Exception("Not enough {$land_display_name}");
    }

    // Check if user has enough resources for all factories
    foreach ($factory_data['construction_cost'] as $cost) {
        $total_cost = $cost['amount'] * $amount;
        $resource = $cost['resource'];
        if ($user_resources[$resource] < $total_cost) {
            $resource_display_name = getResourceDisplayName($resource);
            throw new Exception("Not enough {$resource_display_name}");
        }
    }

    // Deduct land
    $stmt = $pdo->prepare("UPDATE land SET $land_type = $land_type - ? WHERE id = ?");
    $stmt->execute([$total_land_required, $user_id]);

    // Convert to used land
    $stmt = $pdo->prepare("UPDATE land SET used_land = used_land + ? WHERE id = ?");
    $stmt->execute([$total_land_required, $user_id]);

    // Deduct resources
    foreach ($factory_data['construction_cost'] as $cost) {
        $total_cost = $cost['amount'] * $amount;
        $resource = $cost['resource'];
        $stmt = $pdo->prepare("UPDATE commodities SET $resource = $resource - ? WHERE id = ?");
        $stmt->execute([$total_cost, $user_id]);
    }

    // Add to factory queue
    for ($i = 0; $i < $amount; $i++) {
        $stmt = $pdo->prepare("
            INSERT INTO factory_queue (id, factory_type, minutes_left) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user_id, $factory_type, $factory_data['construction_time']]);
    }

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'message' => "Started construction of $amount " . 
                    ($amount === 1 ? $factory_data['name'] : $factory_data['name'] . 's')
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'error_details' => $e->getMessage()
    ]);
}