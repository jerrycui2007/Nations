<?php
global $conn;
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

// Start transaction
$conn->begin_transaction();

try {
    // Check if user has enough money and cleared land
    $stmt = $conn->prepare("SELECT money FROM commodities WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_money = $result->fetch_assoc()['money'];

    $stmt = $conn->prepare("SELECT cleared_land, urban_areas FROM land WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $land_data = $result->fetch_assoc();

    if ($user_money < $total_cost) {
        throw new Exception("Not enough money to build Urban Areas");
    }

    if ($land_data['cleared_land'] < $amount) {
        throw new Exception("Not enough Cleared Land to build Urban Areas");
    }

    // Update money
    $stmt = $conn->prepare("UPDATE commodities SET money = money - ? WHERE id = ?");
    $stmt->bind_param("di", $total_cost, $user_id);
    $stmt->execute();

    // Update land
    $stmt = $conn->prepare("UPDATE land SET cleared_land = cleared_land - ?, urban_areas = urban_areas + ? WHERE id = ?");
    $stmt->bind_param("iii", $amount, $amount, $user_id);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully built $amount Urban Areas"
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}

$conn->close();
