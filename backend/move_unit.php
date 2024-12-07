<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$unit_id = $_POST['unit_id'] ?? '';
$new_division_id = $_POST['division_id'] ?? '';

try {
    // Check if unit belongs to user and get current division
    $stmt = $pdo->prepare("
        SELECT u.type, u.name, u.division_id, d.in_combat as current_in_combat 
        FROM units u 
        LEFT JOIN divisions d ON u.division_id = d.division_id 
        WHERE u.unit_id = ? AND u.player_id = ?
    ");
    $stmt->execute([$unit_id, $user_id]);
    $unit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$unit) {
        throw new Exception("Unit not found or doesn't belong to you");
    }

    // Check if current division is in combat
    if ($unit['current_in_combat']) {
        throw new Exception("Cannot move units from a division that is in combat");
    }

    if ($new_division_id != 0) {  // Skip checks for reserves
        // Check if target division belongs to user and get combat status
        $stmt = $pdo->prepare("
            SELECT user_id, in_combat, mobilization_state, is_defence 
            FROM divisions 
            WHERE division_id = ?
        ");
        $stmt->execute([$new_division_id]);
        $division = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$division || $division['user_id'] != $user_id) {
            throw new Exception("Division not found or doesn't belong to you");
        }

        if ($division['in_combat']) {
            throw new Exception("Cannot move units into a division that is in combat");
        }

        if ($division['mobilization_state'] !== 'demobilized' && $division['is_defence'] !== 1) {
            throw new Exception("Cannot move units into a mobilized or mobilizing division unless it's a defense division");
        }

        // Count units in target division
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM units WHERE division_id = ?");
        $stmt->execute([$new_division_id]);
        if ($stmt->fetchColumn() >= 15) {
            throw new Exception("Division is at maximum capacity (15 units)");
        }

        // Count medics in target division if moving a medic
        if ($unit['name'] === 'Medic') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM units WHERE division_id = ? AND name = 'Medic'");
            $stmt->execute([$new_division_id]);
            if ($stmt->fetchColumn() >= 2) {
                throw new Exception("Division already has maximum number of medics (2)");
            }
        }
    }

    // Move the unit
    $stmt = $pdo->prepare("UPDATE units SET division_id = ? WHERE unit_id = ? AND player_id = ?");
    $stmt->execute([$new_division_id, $unit_id, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Unit moved successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 