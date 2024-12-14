<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$division_id = $_POST['division_id'] ?? null;

if (!$division_id) {
    echo json_encode(['success' => false, 'message' => 'Missing division ID']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Check if division exists and belongs to user
    $stmt = $pdo->prepare("SELECT * FROM divisions WHERE division_id = ? AND user_id = ?");
    $stmt->execute([$division_id, $_SESSION['user_id']]);
    $division = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$division) {
        throw new Exception('Division not found or does not belong to you');
    }

    if ($division['in_combat']) {
        throw new Exception('Cannot modify equipment while division is in combat');
    }

    // Get all units in the division
    $stmt = $pdo->prepare("SELECT unit_id FROM units WHERE division_id = ?");
    $stmt->execute([$division_id]);
    $units = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($units as $unit_id) {
        // Get unit's current stats and equipment
        $stmt = $pdo->prepare("SELECT * FROM units WHERE unit_id = ?");
        $stmt->execute([$unit_id]);
        $unit = $stmt->fetch(PDO::FETCH_ASSOC);

        // Process each equipment slot
        for ($slot = 1; $slot <= 4; $slot++) {
            $equipment_id = $unit["equipment_{$slot}_id"];
            if ($equipment_id) {
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
                                $max_hp -= $buff['actual_value'];
                                $hp = min($hp, $max_hp);
                            } else {
                                $health_multiplier = 1 / (1 + ($buff['value'] / 100));
                                $max_hp = floor($max_hp * $health_multiplier);
                                $hp = floor($hp * $health_multiplier);
                            }
                            break;
                        case 'Buff':
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
            }
        }
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Equipment removed from all units in division'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
