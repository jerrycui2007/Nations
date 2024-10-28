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
    // Fetch trade details
    $stmt = $conn->prepare("SELECT * FROM trades WHERE trade_id = ?");
    $stmt->bind_param("i", $trade_id);
    $stmt->execute();
    $trade = $stmt->get_result()->fetch_assoc();

    if (!$trade) {
        throw new Exception("Trade not found");
    }

    if ($trade['seller_id'] == $user_id) {
        throw new Exception("You cannot purchase your own trade");
    }

    // Calculate total cost
    $total_cost = $trade['amount_offered'] * $trade['price_per_unit'];

    // Check if buyer has enough money
    $stmt = $conn->prepare("SELECT money FROM commodities WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $buyer_money = $stmt->get_result()->fetch_assoc()['money'];

    if ($buyer_money < $total_cost) {
        throw new Exception("You don't have enough money to complete this trade");
    }

    // Transfer money from buyer to seller
    $stmt = $conn->prepare("UPDATE commodities SET money = money - ? WHERE id = ?");
    $stmt->bind_param("di", $total_cost, $user_id);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE commodities SET money = money + ? WHERE id = ?");
    $stmt->bind_param("di", $total_cost, $trade['seller_id']);
    $stmt->execute();

    // Transfer resources from trade to buyer
    $stmt = $conn->prepare("UPDATE commodities SET {$trade['resource_offered']} = {$trade['resource_offered']} + ? WHERE id = ?");
    $stmt->bind_param("ii", $trade['amount_offered'], $user_id);
    $stmt->execute();

    // Record in trade history
    $stmt = $conn->prepare("INSERT INTO trade_history (trade_id, buyer_id, seller_id, resource_offered, amount_offered, price_per_unit, date_finished) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiisid", $trade_id, $user_id, $trade['seller_id'], $trade['resource_offered'], $trade['amount_offered'], $trade['price_per_unit']);
    $stmt->execute();

    // Delete the completed trade
    $stmt = $conn->prepare("DELETE FROM trades WHERE trade_id = ?");
    $stmt->bind_param("i", $trade_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Trade completed successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
