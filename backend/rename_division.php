<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$division_id = $_POST['division_id'] ?? '';
$new_name = trim($_POST['new_name'] ?? '');

if (empty($new_name)) {
    echo json_encode(['success' => false, 'message' => 'Division name cannot be empty']);
    exit();
}

if (strlen($new_name) > 50) {
    echo json_encode(['success' => false, 'message' => 'Division name cannot exceed 50 characters']);
    exit();
}

try {
    // Check if division belongs to user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM divisions WHERE division_id = ? AND user_id = ?");
    $stmt->execute([$division_id, $user_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Division not found or does not belong to you']);
        exit();
    }

    // Check if name already exists for this user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM divisions WHERE user_id = ? AND name = ? AND division_id != ?");
    $stmt->execute([$user_id, $new_name, $division_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'A division with this name already exists']);
        exit();
    }

    // Update division name
    $stmt = $pdo->prepare("UPDATE divisions SET name = ? WHERE division_id = ? AND user_id = ?");
    $stmt->execute([$new_name, $division_id, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Division renamed successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while renaming the division']);
} 