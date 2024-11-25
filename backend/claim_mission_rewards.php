<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

session_start();
require_once 'db_connection.php';
require_once 'mission_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$mission_id = $_POST['mission_id'] ?? '';
$mission_type = $_POST['mission_type'] ?? '';

try {
    $pdo->beginTransaction();

    // Verify mission exists and belongs to user
    $stmt = $pdo->prepare("
        SELECT * FROM missions 
        WHERE mission_id = ? 
        AND user_id = ? 
        AND status = 'complete'
    ");
    $stmt->execute([$mission_id, $user_id]);
    $mission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mission) {
        throw new Exception("Mission not found, not completed, or rewards already claimed");
    }

    // Get mission configuration
    $mission_config = $MISSION_CONFIG[$mission_type] ?? null;
    if (!$mission_config) {
        throw new Exception("Invalid mission type");
    }

    // Grant rewards
    foreach ($mission_config['rewards'] as $reward) {
        $stmt = $pdo->prepare("
            UPDATE commodities 
            SET {$reward['resource']} = {$reward['resource']} + ? 
            WHERE id = ?
        ");
        $stmt->execute([$reward['amount'], $user_id]);
    }

    // Mark rewards as claimed (removed this as it was causing issues)
    $stmt = $pdo->prepare("
        UPDATE missions 
        SET rewards_claimed = TRUE 
        WHERE mission_id = ?
    ");
    $stmt->execute([$mission_id]);

    // Get user's current missions
    $stmt = $pdo->prepare("
        SELECT mission_type 
        FROM missions 
        WHERE user_id = ? 
        AND mission_id != ?
    ");
    $stmt->execute([$user_id, $mission_id]);
    $existing_missions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Generate new random mission
    $total_weight = 0;
    $available_missions = array_diff_key($MISSION_CONFIG, array_flip($existing_missions));

    foreach ($available_missions as $type => $config) {
        $total_weight += $config['spawn_weight'];
    }

    $random = rand(1, $total_weight);
    $current_weight = 0;
    $new_mission_type = '';

    foreach ($available_missions as $type => $config) {
        $current_weight += $config['spawn_weight'];
        if ($random <= $current_weight) {
            $new_mission_type = $type;
            break;
        }
    }

    // If no available missions (shouldn't happen with current config), reuse any mission type
    if (!$new_mission_type) {
        $mission_types = array_keys($MISSION_CONFIG);
        $new_mission_type = $mission_types[array_rand($mission_types)];
    }

    // Update mission with new type
    $stmt = $pdo->prepare("
        UPDATE missions 
        SET mission_type = ?, status = 'incomplete', battle_id = NULL 
        WHERE mission_id = ?
    ");
    $stmt->execute([$new_mission_type, $mission_id]);

    $pdo->commit();

    // Create reward message
    $reward_strings = [];
    foreach ($mission_config['rewards'] as $reward) {
        $reward_strings[] = number_format($reward['amount']) . " " . ucfirst($reward['resource']);
    }
    $reward_message = "Mission completed! Received: " . implode(", ", $reward_strings);

    echo json_encode(['success' => true, 'message' => $reward_message]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Mission Reward Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
