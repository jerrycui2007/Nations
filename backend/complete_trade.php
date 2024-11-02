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

    // Fetch trade details
    $stmt = $pdo->prepare("SELECT * FROM trades WHERE trade_id = ?");
    $stmt->execute([$trade_id]);
    $trade = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trade) {
        throw new Exception("Trade not found");
    }

    if ($trade['seller_id'] == $user_id) {
        throw new Exception("You cannot purchase your own trade");
    }

    // Calculate total cost
    $total_cost = $trade['amount_offered'] * $trade['price_per_unit'];

    // Check if buyer has enough money
    $stmt = $pdo->prepare("SELECT money FROM commodities WHERE id = ?");
    $stmt->execute([$user_id]);
    $buyer_money = $stmt->fetch(PDO::FETCH_ASSOC)['money'];

    if ($buyer_money < $total_cost) {
        throw new Exception("You don't have enough money to complete this trade");
    }

    // Transfer money from buyer to seller
    $stmt = $pdo->prepare("UPDATE commodities SET money = money - ? WHERE id = ?");
    $stmt->execute([$total_cost, $user_id]);

    $stmt = $pdo->prepare("UPDATE commodities SET money = money + ? WHERE id = ?");
    $stmt->execute([$total_cost, $trade['seller_id']]);

    // Transfer resources from trade to buyer
    $stmt = $pdo->prepare("UPDATE commodities 
                          SET `{$trade['resource_offered']}` = `{$trade['resource_offered']}` + ? 
                          WHERE id = ?");
    $stmt->execute([$trade['amount_offered'], $user_id]);

    // Record in trade history
    $stmt = $pdo->prepare("INSERT INTO trade_history 
                          (trade_id, buyer_id, seller_id, resource_offered, amount_offered, price_per_unit, date_finished) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $trade_id,
        $user_id,
        $trade['seller_id'],
        $trade['resource_offered'],
        $trade['amount_offered'],
        $trade['price_per_unit']
    ]);

    // Delete the completed trade
    $stmt = $pdo->prepare("DELETE FROM trades WHERE trade_id = ?");
    $stmt->execute([$trade_id]);

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Trade completed successfully'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
