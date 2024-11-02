<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$land_type = $_POST['land_type'];
$amount = intval($_POST['amount']);

$conversion_costs = [
    'forest' => 100,
    'grassland' => 100,
    'jungle' => 300,
    'desert' => 500,
    'tundra' => 500
];

if (!isset($conversion_costs[$land_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid land type']);
    exit();
}

$cost_per_unit = $conversion_costs[$land_type];
$total_cost = $amount * $cost_per_unit;

try {
    $pdo->beginTransaction();

    // Check if user has enough money and land
    $stmt = $pdo->prepare("SELECT money FROM commodities WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_money = $stmt->fetch(PDO::FETCH_ASSOC)['money'];

    $stmt = $pdo->prepare("SELECT $land_type, cleared_land FROM land WHERE id = ?");
    $stmt->execute([$user_id]);
    $land_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_money < $total_cost) {
        throw new Exception("Not enough money to convert land");
    }

    if ($land_data[$land_type] < $amount) {
        throw new Exception("Not enough {$land_type} to convert");
    }

    // Update money
    $stmt = $pdo->prepare("UPDATE commodities SET money = money - ? WHERE id = ?");
    $stmt->execute([$total_cost, $user_id]);

    // Update land
    $stmt = $pdo->prepare("UPDATE land SET $land_type = $land_type - ?, cleared_land = cleared_land + ? WHERE id = ?");
    $stmt->execute([$amount, $amount, $user_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully converted $amount $land_type to cleared land"
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}