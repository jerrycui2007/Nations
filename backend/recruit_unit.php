<?php
session_start();
require_once 'db_connection.php';
require_once 'unit_config.php';
require_once 'building_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$unit_type = $_POST['unit_type'] ?? '';

if (!isset($UNIT_CONFIG[$unit_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid unit type']);
    exit();
}

$unit_data = $UNIT_CONFIG[$unit_type];

try {
    $pdo->beginTransaction();

    // Get the required building type and level from unit config
    $required_building = array_key_first($unit_data['building_requirements']);
    $required_level = $unit_data['building_requirements'][$required_building];

    // Get current building level and resources
    $stmt = $pdo->prepare("
        SELECT b.$required_building, c.* 
        FROM buildings b 
        JOIN commodities c ON b.id = c.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_level = $user_data[$required_building];

    // Check if user has enough resources
    foreach ($unit_data['recruitment_cost'] as $resource => $amount) {
        if ($user_data[$resource] < $amount) {
            throw new Exception("Insufficient " . ucfirst($resource) . " to recruit unit");
        }
    }

    // Calculate maximum allowed units (level * 3)
    $max_allowed = $current_level * 3;

    // Count current units in queue
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM unit_queue WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_queue = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($current_queue >= $max_allowed) {
        throw new Exception("Maximum recruitment queue reached ($max_allowed units)");
    }

    // Deduct resources
    $updates = [];
    $params = [];
    foreach ($unit_data['recruitment_cost'] as $resource => $amount) {
        $updates[] = "$resource = $resource - ?";
        $params[] = $amount;
    }
    $params[] = $user_id;

    $stmt = $pdo->prepare("
        UPDATE commodities 
        SET " . implode(', ', $updates) . "
        WHERE id = ?
    ");
    $stmt->execute($params);

    // Insert into queue
    $stmt = $pdo->prepare("
        INSERT INTO unit_queue (id, unit_type, minutes_left) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $unit_type, $unit_data['recruitment_time']]);

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'message' => "Started recruitment of {$unit_data['name']}"
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
