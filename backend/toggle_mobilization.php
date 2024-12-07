<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$division_id = $_POST['division_id'] ?? '';
$new_state = $_POST['new_state'] ?? '';

// Validate new state
$valid_states = ['demobilized', 'mobilizing', 'mobilized'];
if (!in_array($new_state, $valid_states)) {
    echo json_encode(['success' => false, 'message' => 'Invalid mobilization state']);
    exit();
}

try {
    // Check if division belongs to user
    $stmt = $pdo->prepare("
        SELECT * FROM divisions 
        WHERE division_id = ? AND user_id = ?
    ");
    $stmt->execute([$division_id, $user_id]);
    $division = $stmt->fetch();

    if (!$division) {
        throw new Exception("Division not found or doesn't belong to you");
    }

    // Check if division is in combat
    if ($division['in_combat']) {
        throw new Exception("Cannot change mobilization state of a division in combat");
    }

    // Update mobilization state
    $stmt = $pdo->prepare("
        UPDATE divisions 
        SET mobilization_state = ? 
        WHERE division_id = ?
    ");
    $stmt->execute([$new_state, $division_id]);

    $message = "Division mobilization state updated to " . ucfirst($new_state);
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
