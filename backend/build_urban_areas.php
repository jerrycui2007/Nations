<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = intval($_POST['amount']);
$cost_per_unit = 500;
$total_cost = $amount * $cost_per_unit;

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if user has enough money and cleared land
    $stmt = $pdo->prepare("SELECT money FROM commodities WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_money = $stmt->fetch(PDO::FETCH_ASSOC)['money'];

    $stmt = $pdo->prepare("SELECT cleared_land, urban_areas FROM land WHERE id = ?");
    $stmt->execute([$user_id]);
    $land_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_money < $total_cost) {
        throw new Exception("Not enough money to build Urban Areas");
    }

    if ($land_data['cleared_land'] < $amount) {
        throw new Exception("Not enough Cleared Land to build Urban Areas");
    }

    // Update money
    $stmt = $pdo->prepare("UPDATE commodities SET money = money - ? WHERE id = ?");
    $stmt->execute([$total_cost, $user_id]);

    // Update land
    $stmt = $pdo->prepare("UPDATE land SET cleared_land = cleared_land - ?, urban_areas = urban_areas + ? WHERE id = ?");
    $stmt->execute([$amount, $amount, $user_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully built $amount Urban Areas"
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}
