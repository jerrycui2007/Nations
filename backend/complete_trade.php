<?php
session_start();
require_once 'db_connection.php';
require_once 'resource_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$trade_id = $_POST['trade_id'] ?? 0;
$purchase_amount = intval($_POST['amount'] ?? 0);

try {
    $pdo->beginTransaction();

    // Fetch trade details with lock
    $stmt = $pdo->prepare("SELECT * FROM trades WHERE trade_id = ? FOR UPDATE");
    $stmt->execute([$trade_id]);
    $trade = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trade) {
        throw new Exception("Trade not found");
    }

    if ($purchase_amount <= 0 || $purchase_amount > $trade['amount_offered']) {
        throw new Exception("Invalid purchase amount");
    }

    $total_cost = $purchase_amount * $trade['price_per_unit'];
    
    // Check buyer's money
    $stmt = $pdo->prepare("SELECT money FROM commodities WHERE id = ? FOR UPDATE");
    $stmt->execute([$_SESSION['user_id']]);
    $buyer_resources = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($buyer_resources['money'] < $total_cost) {
        throw new Exception("Not enough money");
    }

    // Process the trade
    // Update buyer's resources
    $stmt = $pdo->prepare("UPDATE commodities SET 
        money = money - ?,
        {$trade['resource_offered']} = {$trade['resource_offered']} + ?
        WHERE id = ?");
    $stmt->execute([$total_cost, $purchase_amount, $_SESSION['user_id']]);

    // Update seller's resources
    $stmt = $pdo->prepare("UPDATE commodities SET money = money + ? WHERE id = ?");
    $stmt->execute([$total_cost, $trade['seller_id']]);

    // Update or remove trade listing
    if ($purchase_amount == $trade['amount_offered']) {
        $stmt = $pdo->prepare("DELETE FROM trades WHERE trade_id = ?");
        $stmt->execute([$trade_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE trades SET amount_offered = amount_offered - ? WHERE trade_id = ?");
        $stmt->execute([$purchase_amount, $trade_id]);
    }

    // Add to trade history
    $stmt = $pdo->prepare("INSERT INTO trade_history (buyer_id, seller_id, resource_offered, amount_offered, price_per_unit, date_finished)
        VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $trade['seller_id'], $trade['resource_offered'], $purchase_amount, $trade['price_per_unit']]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Trade completed successfully']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
