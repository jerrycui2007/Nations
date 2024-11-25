<?php
session_start();
require_once 'db_connection.php';
require_once 'unit_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$unit_id = $_POST['unit_id'] ?? '';
$refund_costs = json_decode($_POST['refund_costs'], true) ?? [];

if (!is_array($refund_costs)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid refund costs format']);
    exit();
}

try {
    $pdo->beginTransaction();

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
        throw new Exception("Cannot disband units from a division that is in combat");
    }

    // Delete buffs first (foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM buffs WHERE unit_id = ? AND unit_id IN (SELECT unit_id FROM units WHERE player_id = ?)");
    $stmt->execute([$unit_id, $user_id]);

    // Delete the unit
    $stmt = $pdo->prepare("DELETE FROM units WHERE unit_id = ? AND player_id = ?");
    $stmt->execute([$unit_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Unit not found or doesn't belong to you");
    }

    // Refund 50% of recruitment costs
    $updates = [];
    $params = [];
    foreach ($refund_costs as $resource => $amount) {
        $refund_amount = floor($amount * 0.5); // 50% refund
        if ($refund_amount > 0) {
            $updates[] = "$resource = $resource + ?";
            $params[] = $refund_amount;
        }
    }

    if (!empty($updates)) {
        $params[] = $user_id;
        $stmt = $pdo->prepare("
            UPDATE commodities 
            SET " . implode(', ', $updates) . "
            WHERE id = ?
        ");
        $stmt->execute($params);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Unit disbanded successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 