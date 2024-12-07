<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$equipment_id = $_POST['equipment_id'] ?? '';
$new_name = $_POST['new_name'] ?? '';

if (empty($new_name)) {
    echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
    exit();
}

try {
    // Check if equipment belongs to user
    $stmt = $pdo->prepare("UPDATE equipment SET name = ? WHERE equipment_id = ? AND user_id = ?");
    $stmt->execute([$new_name, $equipment_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Equipment not found or doesn't belong to you");
    }

    echo json_encode(['success' => true, 'message' => 'Equipment renamed successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 