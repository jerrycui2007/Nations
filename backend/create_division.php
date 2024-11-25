<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Division name cannot be empty']);
    exit();
}

if (strlen($name) > 50) {
    echo json_encode(['success' => false, 'message' => 'Division name cannot exceed 50 characters']);
    exit();
}

try {
    // Check if division name already exists for this user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM divisions WHERE user_id = ? AND name = ?");
    $stmt->execute([$user_id, $name]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'A division with this name already exists']);
        exit();
    }

    // Create the division
    $stmt = $pdo->prepare("INSERT INTO divisions (user_id, name) VALUES (?, ?)");
    $stmt->execute([$user_id, $name]);

    echo json_encode(['success' => true, 'message' => 'Division created successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while creating the division']);
} 