<?php
// Turn off error display and log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

session_start();
require_once 'db_connection.php';
require_once 'unit_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$division_id = $_POST['division_id'] ?? '';

try {
    $pdo->beginTransaction();

    // Get division info and count units
    $stmt = $pdo->prepare("
        SELECT d.name, d.is_defence, d.in_combat, COUNT(u.unit_id) as unit_count 
        FROM divisions d 
        LEFT JOIN units u ON d.division_id = u.division_id 
        WHERE d.division_id = ? AND d.user_id = ?
        GROUP BY d.division_id
    ");
    $stmt->execute([$division_id, $user_id]);
    $division = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$division) {
        throw new Exception("Division not found or doesn't belong to you");
    }

    if ($division['is_defence']) {
        throw new Exception("Cannot send defensive division on peacekeeping");
    }

    if ($division['in_combat']) {
        throw new Exception("Cannot send division that is already in combat");
    }

    // Check if division has any units
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM units WHERE division_id = ?");
    $stmt->execute([$division_id]);
    $unit_count = $stmt->fetchColumn();

    if ($unit_count === 0) {
        throw new Exception("Cannot send empty division on peacekeeping mission");
    }

    // Create NPC division
    $stmt = $pdo->prepare("
        INSERT INTO divisions (user_id, name, is_defence, in_combat) 
        VALUES (0, 'Oodlistan ISR Insurgents', 0, 1)
    ");
    $stmt->execute();
    $npc_division_id = $pdo->lastInsertId();

    // Calculate player division strength
    $stmt = $pdo->prepare("
        SELECT firepower, armour, maneuver, hp, max_hp 
        FROM units 
        WHERE division_id = ?
    ");
    $stmt->execute([$division_id]);
    $player_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_strength = 0;
    foreach ($player_units as $unit) {
        $total_strength += $unit['firepower'] + $unit['armour'] + $unit['maneuver'] + floor($unit['hp'] / 10);
    }

    // Create enough NPC units to match 1/2 the total strength
    $target_npc_strength = floor($total_strength / 2);
    $npc_strength = 0;
    $unit_config = $UNIT_CONFIG['ak47_infantry'];
    $single_unit_strength = $unit_config['firepower'] + $unit_config['armour'] + 
                           $unit_config['maneuver'] + floor($unit_config['hp'] / 10);

    while ($npc_strength < $target_npc_strength) {
        $stmt = $pdo->prepare("
            INSERT INTO units (
                player_id, name, custom_name, type, level, xp, division_id,
                firepower, armour, maneuver, max_hp, hp,
                equipment_1_id, equipment_2_id, equipment_3_id, equipment_4_id
            ) VALUES (
                0, ?, ?, ?, 1, 0, ?,
                ?, ?, ?, ?, ?,
                0, 0, 0, 0
            )
        ");

        $stmt->execute([
            $unit_config['name'],
            $unit_config['name'],
            $unit_config['type'],
            $npc_division_id,
            $unit_config['firepower'],
            $unit_config['armour'],
            $unit_config['maneuver'],
            $unit_config['hp'],
            $unit_config['hp']
        ]);
        
        $npc_strength += $single_unit_strength;
    }

    // Create the battle
    $stmt = $pdo->prepare("
        INSERT INTO battles (
            is_multiplayer, continent, battle_name, 
            defender_name, attacker_name,
            defender_division_id, attacker_division_id
        ) VALUES (
            0, 'zaheria', 'Peacekeeping Mission',
            'Oodlistan ISR Insurgents', ?,
            ?, ?
        )
    ");
    
    if (!$stmt->execute([$division['name'], $npc_division_id, $division_id])) {
        throw new Exception("Failed to create peacekeeping mission: " . implode(" ", $stmt->errorInfo()));
    }

    // Get the battle ID - modified to use MAX instead of lastInsertId
    $stmt = $pdo->prepare("SELECT MAX(battle_id) as battle_id FROM battles");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $battle_id = $result['battle_id'];

    // Update division status
    $stmt = $pdo->prepare("UPDATE divisions SET in_combat = 1 WHERE division_id = ?");
    if (!$stmt->execute([$division_id])) {
        throw new Exception("Failed to update division status: " . implode(" ", $stmt->errorInfo()));
    }

    // Get user's nation name
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $nation = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create notification
    $notification_message = "<a href='view.php?id={$nation['id']}'>" . htmlspecialchars($nation['country_name']) . "</a> sent a division on a <a href='battle.php?battle_id={$battle_id}'>Peacekeeping Mission.</a>";

    $stmt = $pdo->prepare("
        INSERT INTO notifications (type, message, date) 
        VALUES ('Conflict', ?, NOW())
    ");
    $stmt->execute([$notification_message]);

    // Create initial battle report
    $stmt = $pdo->prepare("
        INSERT INTO battle_reports (user_id, battle_id, message, visible) 
        VALUES (?, ?, '', 0)
    ");
    $stmt->execute([$user_id, $battle_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Division sent on peacekeeping mission']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Peacekeeping Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Error $e) {
    $pdo->rollBack();
    error_log("Peacekeeping Fatal Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred']);
}
