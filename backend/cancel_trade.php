<?php
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

try {
    // Start transaction
    $pdo->beginTransaction();

    // Fetch trade details and verify ownership
    $stmt = $pdo->prepare("SELECT * FROM trades WHERE trade_id = ? AND seller_id = ?");
    $stmt->execute([$trade_id, $user_id]);
    $trade = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trade) {
        throw new Exception("Trade not found or you don't have permission to cancel it");
    }

    // Return resources to seller
    $stmt = $pdo->prepare("UPDATE commodities 
                          SET `{$trade['resource_offered']}` = `{$trade['resource_offered']}` + ? 
                          WHERE id = ?");
    $stmt->execute([$trade['amount_offered'], $user_id]);

    // Delete the trade
    $stmt = $pdo->prepare("DELETE FROM trades WHERE trade_id = ?");
    $stmt->execute([$trade_id]);

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Trade cancelled successfully. Resources have been returned to your inventory.'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
