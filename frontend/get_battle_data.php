<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure no other output before JSON
ob_clean();

session_start();
require_once '../backend/db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['battle_id'])) {
    http_response_code(400);
    exit();
}

$battle_id = intval($_GET['battle_id']);

// Fetch battle data
$stmt = $pdo->prepare("SELECT *, 
    CASE 
        WHEN is_over = 1 THEN winner_name
        ELSE NULL 
    END as winner 
    FROM battles WHERE battle_id = ?");
$stmt->execute([$battle_id]);
$battle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$battle) {
    http_response_code(404);
    exit();
}

// Fetch defending division and its units
$stmt = $pdo->prepare("
    SELECT d.*, u.name as unit_name, u.custom_name, u.unit_id, 
           u.level, u.firepower, u.armour, u.maneuver, u.hp,
           u.max_hp
    FROM divisions d
    JOIN units u ON d.division_id = u.division_id
    WHERE d.division_id = ?
");
$stmt->execute([$battle['defender_division_id']]);
$defending_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch attacking division and its units
$stmt = $pdo->prepare("
    SELECT d.*, u.name as unit_name, u.custom_name, u.unit_id, 
           u.level, u.firepower, u.armour, u.maneuver, u.hp,
           u.max_hp
    FROM divisions d
    JOIN units u ON d.division_id = u.division_id
    WHERE d.division_id = ?
");
$stmt->execute([$battle['attacker_division_id']]);
$attacking_units = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch combat reports for this battle
$stmt = $pdo->prepare("
    SELECT time, message 
    FROM combat_reports 
    WHERE battle_id = ? 
    ORDER BY time DESC
");
$stmt->execute([$battle_id]);
$combat_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate strengths using the same function from battle.php
function calculateDivisionStrength($units) {
    $total_firepower = 0;
    $total_armour = 0;
    $total_maneuver = 0;
    $total_hp = 0;
    
    foreach ($units as $unit) {
        if ($unit['hp'] > 0) {  // Only count stats for living units
            $total_firepower += $unit['firepower'];
            $total_armour += $unit['armour'];
            $total_maneuver += $unit['maneuver'];
            $total_hp += floor($unit['hp'] / 10);
        }
    }
    
    return $total_firepower + $total_armour + $total_maneuver + $total_hp;
}

$defender_strength = calculateDivisionStrength($defending_units);
$attacker_strength = calculateDivisionStrength($attacking_units);

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'is_over' => (bool)$battle['is_over'],
    'winner' => $battle['winner'],
    'defending_units' => $defending_units,
    'attacking_units' => $attacking_units,
    'defender_strength' => $defender_strength,
    'attacker_strength' => $attacker_strength,
    'combat_reports' => $combat_reports
]);
