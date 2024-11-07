<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = intval($_POST['amount']);
$cost_per_unit = 1000;
$total_cost = $amount * $cost_per_unit;

try {
    $pdo->beginTransaction();

    // Check if user has enough money
    $stmt = $pdo->prepare("SELECT money FROM commodities WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_money = $stmt->fetch(PDO::FETCH_ASSOC)['money'];

    if ($user_money < $total_cost) {
        throw new Exception("Not enough money to buy Cleared Land");
    }

    // Update money and land
    $stmt = $pdo->prepare("UPDATE commodities SET money = money - ? WHERE id = ?");
    $stmt->execute([$total_cost, $user_id]);

    $stmt = $pdo->prepare("UPDATE land SET cleared_land = cleared_land + ? WHERE id = ?");
    $stmt->execute([$amount, $user_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully purchased $amount Cleared Land"
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 