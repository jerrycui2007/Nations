<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once 'db_connection.php';
require_once 'equipment_config.php';

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

    // Check if equipment exists and belongs to user
    $stmt = $pdo->prepare("
        SELECT e.*, 
               CASE WHEN (u.equipment_1_id = e.equipment_id OR 
                         u.equipment_2_id = e.equipment_id OR 
                         u.equipment_3_id = e.equipment_id OR 
                         u.equipment_4_id = e.equipment_id) 
                    THEN u.unit_id 
                    ELSE NULL 
               END as unit_id
        FROM equipment e
        LEFT JOIN units u ON (e.equipment_id = u.equipment_1_id OR 
                             e.equipment_id = u.equipment_2_id OR 
                             e.equipment_id = u.equipment_3_id OR 
                             e.equipment_id = u.equipment_4_id)
        WHERE e.equipment_id = ? AND e.user_id = ?
    ");
    $stmt->execute([$equipment_id, $_SESSION['user_id']]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipment) {
        throw new Exception('Equipment not found or does not belong to you');
    }

    // Check if unit meets minimum level requirement for equipment rarity
    $minimum_level = $MINIMUM_LEVEL_CONFIG[$equipment['rarity']];
    if ($unit['level'] < $minimum_level) {
        throw new Exception("Unit must be at least level {$minimum_level} to equip {$equipment['rarity']} equipment");
    }

    // Verify equipment type matches slot type
    $unit_type = $unit['type'];
    $required_type = $TYPE_CONFIG[$unit_type][$slot];
    
    if ($equipment['type'] !== $required_type) {
        throw new Exception('Equipment type does not match slot type');
    }

    // Check if equipment is already equipped
    $stmt = $pdo->prepare("
        SELECT 1 FROM units 
        WHERE (equipment_1_id = ? OR equipment_2_id = ? OR equipment_3_id = ? OR equipment_4_id = ?)
    ");
    $stmt->execute([$equipment_id, $equipment_id, $equipment_id, $equipment_id]);
    if ($stmt->fetch()) {
        throw new Exception('Equipment is already equipped on another unit');
    }

    // Get equipment buffs
    $stmt = $pdo->prepare("
        SELECT buff_type, value 
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

    // Apply buffs
    foreach ($buffs as $buff) {
        switch ($buff['buff_type']) {
            case 'Firepower':
                $firepower += $buff['value'];
                break;
            case 'Armour':
                $armour += $buff['value'];
                break;
            case 'Maneuver':
                $maneuver += $buff['value'];
                break;
            case 'Health':
                $health_multiplier = 1 + ($buff['value'] / 100);
                $health_increase = floor($max_hp * $health_multiplier) - $max_hp;
                $max_hp = floor($max_hp * $health_multiplier);
                $hp = floor($hp * $health_multiplier);
                
                // Store the actual health increase
                $stmt = $pdo->prepare("UPDATE equipment_buffs SET actual_value = ? WHERE equipment_id = ? AND buff_type = 'Health'");
                $stmt->execute([$health_increase, $equipment_id]);
                break;
            case 'Buff':
                // Update the unit_id for this buff
                $stmt = $pdo->prepare("UPDATE buffs SET unit_id = ? WHERE buff_id = ?");
                $stmt->execute([$unit_id, $buff['value']]);
                break;
        }
    }

    // Update unit's stats and equipment slot
    $stmt = $pdo->prepare("
        UPDATE units 
        SET equipment_{$slot}_id = ?,
            firepower = ?,
            armour = ?,
            maneuver = ?,
            hp = ?,
            max_hp = ?
        WHERE unit_id = ?
    ");
    $stmt->execute([$equipment_id, $firepower, $armour, $maneuver, $hp, $max_hp, $unit_id]);

    // Update equipment's unit_id
    $stmt = $pdo->prepare("UPDATE equipment SET unit_id = ? WHERE equipment_id = ?");
    $stmt->execute([$unit_id, $equipment_id]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 