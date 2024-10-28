<?php
global $conn;
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$trade_id = intval($_POST['trade_id'] ?? 0);

if ($trade_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid trade ID']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Fetch trade details and verify ownership
    $stmt = $conn->prepare("SELECT * FROM trades WHERE trade_id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $trade_id, $user_id);
    $stmt->execute();
    $trade = $stmt->get_result()->fetch_assoc();

    if (!$trade) {
        throw new Exception("Trade not found or you don't have permission to cancel it");
    }

    // Return resources to seller
    $stmt = $conn->prepare("UPDATE commodities 
                           SET {$trade['resource_offered']} = {$trade['resource_offered']} + ? 
                           WHERE id = ?");
    $stmt->bind_param("ii", $trade['amount_offered'], $user_id);
    $stmt->execute();

    // Delete the trade
    $stmt = $conn->prepare("DELETE FROM trades WHERE trade_id = ?");
    $stmt->bind_param("i", $trade_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Trade cancelled successfully. Resources have been returned to your inventory.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
