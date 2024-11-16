<?php
error_reporting(0); // Disable error reporting
header('Content-Type: application/json');
session_start();
require_once 'db_connection.php';
require_once 'resource_config.php';
$GLOBALS['JSON_API'] = true;
require_once '../frontend/helpers/resource_display.php';

// Ensure we only output JSON
header('Content-Type: application/json');

// Function to handle errors
function handleError($message) {
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    handleError('Not logged in');
}

// Get and validate input parameters
$trade_id = intval($_POST['trade_id'] ?? 0);
$purchase_amount = intval($_POST['amount'] ?? 0);

if ($trade_id <= 0 || $purchase_amount <= 0) {
    handleError('Invalid trade parameters');
}

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

    // Calculate total cost once at the beginning
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

    // After successful trade completion, before the commit
    // Get nation names for the notification
    $stmt = $pdo->prepare("SELECT id, country_name FROM users WHERE id IN (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $trade['seller_id']]);
    $nations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create an associative array to map IDs to names
    $nation_names = [];
    foreach ($nations as $nation) {
        $nation_names[$nation['id']] = $nation['country_name'];
    }

    // Use the correct names based on IDs
    $buyer_name = "<a href='view.php?id={$_SESSION['user_id']}'>" . htmlspecialchars($nation_names[$_SESSION['user_id']]) . "</a>";
    $seller_name = "<a href='view.php?id={$trade['seller_id']}'>" . htmlspecialchars($nation_names[$trade['seller_id']]) . "</a>";
    $resource_icon = getResourceIcon($trade['resource_offered']);
    $money_icon = getResourceIcon('money');

    $notification_message = "{$buyer_name} bought {$resource_icon}" . number_format($purchase_amount) . 
                           " from {$seller_name} at {$money_icon}" . number_format($trade['price_per_unit']) . 
                           " per unit, for a total of {$money_icon}" . number_format($total_cost) . ".";

    // Insert notification
    $stmt = $pdo->prepare("INSERT INTO notifications (message, type) VALUES (?, 'Trade')");
    $stmt->execute([$notification_message]);

    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Trade completed successfully']);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    handleError($e->getMessage());
}
