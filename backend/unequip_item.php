<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$equipment_id = $_POST['equipment_id'] ?? null;
$unit_id = $_POST['unit_id'] ?? null;
$slot = $_POST['slot'] ?? null;

if (!$equipment_id || !$unit_id || !$slot) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if unit exists and belongs to user
    $stmt = $pdo->prepare("
        SELECT units.*, d.in_combat 
        FROM units
        LEFT JOIN divisions d ON units.division_id = d.division_id
        WHERE units.unit_id = ? AND units.player_id = ?
    ");
    $stmt->execute([$unit_id, $_SESSION['user_id']]);
    $unit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$unit) {
        throw new Exception('Unit not found or does not belong to you');
    }

    if ($unit['in_combat']) {
        throw new Exception('Cannot modify equipment while unit is in combat');
    }

    // Verify the equipment is actually equipped in the specified slot
    $slot_column = "equipment_{$slot}_id";
    if ($unit[$slot_column] != $equipment_id) {
        throw new Exception('Equipment is not equipped in the specified slot');
    }

    // Get equipment buffs
    $stmt = $pdo->prepare("
        SELECT buff_type, value, actual_value 
        FROM equipment_buffs 
        WHERE equipment_id = ?
    ");
    $stmt->execute([$equipment_id]);
    $buffs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize stat changes
    $firepower = $unit['firepower'];
    $armour = $unit['armour'];
    $maneuver = $unit['maneuver'];
    $hp = $unit['hp'];
    $max_hp = $unit['max_hp'];

    // Remove buffs
    foreach ($buffs as $buff) {
        switch ($buff['buff_type']) {
            case 'Firepower':
                $firepower -= $buff['value'];
                break;
            case 'Armour':
                $armour -= $buff['value'];
                break;
            case 'Maneuver':
                $maneuver -= $buff['value'];
                break;
            case 'Health':
                if ($buff['actual_value'] !== null) {
                    // Use the stored actual value
                    $max_hp -= $buff['actual_value'];
                    $hp = min($hp, $max_hp);
                } else {
                    // Use the old percentage-based calculation
                    $health_multiplier = 1 / (1 + ($buff['value'] / 100));
                    $max_hp = floor($max_hp * $health_multiplier);
                    $hp = floor($hp * $health_multiplier);
                }
                break;
            case 'Buff':
                // Reset the unit_id for this buff to 0
                $stmt = $pdo->prepare("UPDATE buffs SET unit_id = 0 WHERE buff_id = ?");
                $stmt->execute([$buff['value']]);
                break;
        }
    }

    // Update unit's stats and equipment slot
    $stmt = $pdo->prepare("
        UPDATE units 
        SET equipment_{$slot}_id = NULL,
            firepower = ?,
            armour = ?,
            maneuver = ?,
            hp = ?,
            max_hp = ?
        WHERE unit_id = ?
    ");
    $stmt->execute([$firepower, $armour, $maneuver, $hp, $max_hp, $unit_id]);

    // Update equipment's unit_id to NULL
    $stmt = $pdo->prepare("UPDATE equipment SET unit_id = NULL WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 