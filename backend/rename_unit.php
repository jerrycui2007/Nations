<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$unit_id = $_POST['unit_id'] ?? '';
$new_name = $_POST['new_name'] ?? '';

if (empty($new_name)) {
    echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
    exit();
}

try {
    // Check if unit's division is in combat
    $stmt = $pdo->prepare("
        SELECT d.in_combat 
        FROM units u 
        LEFT JOIN divisions d ON u.division_id = d.division_id 
        WHERE u.unit_id = ? AND u.player_id = ?
    ");
    $stmt->execute([$unit_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception("Unit not found or doesn't belong to you");
    }

    if ($result['in_combat']) {
        throw new Exception("Cannot rename units in a division that is in combat");
    }

    // Proceed with rename if not in combat
    $stmt = $pdo->prepare("UPDATE units SET custom_name = ? WHERE unit_id = ? AND player_id = ?");
    $stmt->execute([$new_name, $unit_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Unit not found or doesn't belong to you");
    }

    echo json_encode(['success' => true, 'message' => 'Unit renamed successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
