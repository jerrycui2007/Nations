<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$division_id = $_POST['division_id'] ?? '';

try {
    $pdo->beginTransaction();

    // Check if division exists, belongs to user, and is not defensive or in combat
    $stmt = $pdo->prepare("SELECT is_defence, in_combat FROM divisions WHERE division_id = ? AND user_id = ?");
    $stmt->execute([$division_id, $user_id]);
    $division = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$division) {
        throw new Exception("Division not found or doesn't belong to you");
    }

    if ($division['is_defence']) {
        throw new Exception("Cannot disband defensive division");
    }

    if ($division['in_combat']) {
        throw new Exception("Cannot disband division that is in combat");
    }

    // Move all units to reserves
    $stmt = $pdo->prepare("UPDATE units SET division_id = 0 WHERE division_id = ? AND player_id = ?");
    $stmt->execute([$division_id, $user_id]);

    // Delete the division
    $stmt = $pdo->prepare("DELETE FROM divisions WHERE division_id = ? AND user_id = ?");
    $stmt->execute([$division_id, $user_id]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Division disbanded successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
