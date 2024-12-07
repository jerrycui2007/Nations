<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$enabled = isset($_POST['enabled']) && $_POST['enabled'] === 'true';

try {
    $stmt = $pdo->prepare("UPDATE users SET notifications_enabled = ? WHERE id = ?");
    if ($stmt->execute([$enabled, $_SESSION['user_id']])) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification settings updated successfully!'
        ]);
    } else {
        throw new Exception("Failed to update settings");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating notification settings.'
    ]);
}
