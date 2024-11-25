<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

session_start();
require_once 'db_connection.php';
require_once 'unit_config.php';
require_once 'mission_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$mission_id = $_POST['mission_id'] ?? '';
$mission_type = $_POST['mission_type'] ?? '';
$division_id = $_POST['division_id'] ?? '';

try {
    $pdo->beginTransaction();

    // Verify mission exists and belongs to user
    $stmt = $pdo->prepare("SELECT * FROM missions WHERE mission_id = ? AND user_id = ? AND status = 'incomplete'");
    $stmt->execute([$mission_id, $user_id]);
    $mission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mission) {
        throw new Exception("Mission not found or already completed");
    }

    // Get mission configuration
    $mission_config = $MISSION_CONFIG[$mission_type] ?? null;
    if (!$mission_config) {
        throw new Exception("Invalid mission type");
    }

    // Verify division exists and belongs to user
    $stmt = $pdo->prepare("SELECT * FROM divisions WHERE division_id = ? AND user_id = ?");
    $stmt->execute([$division_id, $user_id]);
    $division = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$division) {
        throw new Exception("Division not found or doesn't belong to you");
    }

    if ($division['is_defence']) {
        throw new Exception("Cannot send defensive division on missions");
    }

    if ($division['in_combat']) {
        throw new Exception("Cannot send division that is already in combat");
    }

    // Before creating NPC division
    error_log("Debug - Mission Config: " . print_r($mission_config, true));
    error_log("Debug - Defender Division Name: " . ($mission_config['defender_division_name'] ?? 'NULL'));

    // Create NPC division
    if (!isset($mission_config['defender_division_name']) || empty($mission_config['defender_division_name'])) {
        error_log("Debug - Missing defender_division_name in mission config");
        throw new Exception("Invalid mission configuration: missing defender division name");
    }

    $stmt = $pdo->prepare("
        INSERT INTO divisions (user_id, name, is_defence, in_combat) 
        VALUES (0, ?, 0, 1)
    ");

    error_log("Debug - About to execute division insert with name: " . $mission_config['defender_division_name']);
    $stmt->execute([$mission_config['defender_division_name']]);
    $npc_division_id = $pdo->lastInsertId();
    error_log("Debug - Created NPC division with ID: " . $npc_division_id);

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

    // Create enemy units based on mission configuration
    foreach ($mission_config['enemies'] as $enemy_type => $enemy_data) {
        $unit_config = $UNIT_CONFIG[$enemy_type];
        $amount = rand($enemy_data['min_amount'], $enemy_data['max_amount']);

        for ($i = 0; $i < $amount; $i++) {
            $stmt = $pdo->prepare("
                INSERT INTO units (
                    player_id, name, custom_name, type, level, xp, division_id,
                    firepower, armour, maneuver, max_hp, hp
                ) VALUES (
                    0, ?, ?, ?, 1, 0, ?,
                    ?, ?, ?, ?, ?
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
        }
    }

    // Create the battle
    $stmt = $pdo->prepare("
        INSERT INTO battles (
            is_multiplayer, continent, battle_name, 
            defender_name, attacker_name,
            defender_division_id, attacker_division_id,
            mission_id
        ) VALUES (
            0, ?, ?,
            ?, ?,
            ?, ?,
            ?
        )
    ");
    
    $stmt->execute([
        $mission_config['continent'],
        $mission_config['name'],
        $mission_config['defender_division_name'],
        $division['name'],
        $npc_division_id,
        $division_id,
        $mission_id
    ]);

    $battle_id = $pdo->lastInsertId();

    // Update division status
    $stmt = $pdo->prepare("UPDATE divisions SET in_combat = 1 WHERE division_id = ?");
    $stmt->execute([$division_id]);

    // Update mission status
    $stmt = $pdo->prepare("UPDATE missions SET status = 'in_progress', battle_id = ? WHERE mission_id = ?");
    $stmt->execute([$battle_id, $mission_id]);

    // Create initial battle report
    $stmt = $pdo->prepare("
        INSERT INTO battle_reports (user_id, battle_id, message, visible) 
        VALUES (?, ?, ?, 0)
    ");

    $initial_message = "<h3>" . htmlspecialchars($mission_config['name']) . "</h3>";
    $initial_message .= "<p>" . htmlspecialchars($mission_config['description']) . "</p>";
    $initial_message .= "<p class='battle-start'>" . htmlspecialchars($division['name']) . " has engaged " . htmlspecialchars($mission_config['defender_division_name']) . "!</p>";

    $stmt->execute([$user_id, $battle_id, $initial_message]);

    // Get user's nation name
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $nation = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create notification
    $notification_message = "<a href='view.php?id={$nation['id']}'>" . htmlspecialchars($nation['country_name']) . "</a> sent a division on a <a href='battle.php?battle_id={$battle_id}'>" . htmlspecialchars($mission_config['name']) . "</a> mission.";

    $stmt = $pdo->prepare("
        INSERT INTO notifications (type, message, date) 
        VALUES ('Conflict', ?, NOW())
    ");
    $stmt->execute([$notification_message]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Mission started successfully']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Mission Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
