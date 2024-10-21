<?php
global $conn;
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

// Start transaction
$conn->begin_transaction();

try {
    // Check if user has enough money and land
    $stmt = $conn->prepare("SELECT money FROM commodities WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_money = $result->fetch_assoc()['money'];

    $stmt = $conn->prepare("SELECT $land_type, cleared_land FROM land WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $land_data = $result->fetch_assoc();

    if ($user_money < $total_cost) {
        throw new Exception("Not enough money to convert land");
    }

    if ($land_data[$land_type] < $amount) {
        throw new Exception("Not enough $land_type to convert");
    }

    // Update money
    $stmt = $conn->prepare("UPDATE commodities SET money = money - ? WHERE id = ?");
    $stmt->bind_param("di", $total_cost, $user_id);
    $stmt->execute();

    // Update land
    $stmt = $conn->prepare("UPDATE land SET $land_type = $land_type - ?, cleared_land = cleared_land + ? WHERE id = ?");
    $stmt->bind_param("iii", $amount, $amount, $user_id);
    $stmt->execute();

    $conn->commit();

    // Fetch updated land data
    $stmt = $conn->prepare("SELECT $land_type, cleared_land, (cleared_land + urban_areas + forest + mountain + river + lake + grassland + jungle + desert + tundra) AS total_land FROM land WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $updated_land = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'message' => "Successfully converted $amount $land_type to cleared land",
        'new_amount' => number_format($updated_land[$land_type]),
        'new_cleared_land' => number_format($updated_land['cleared_land']),
        'new_total_land' => number_format($updated_land['total_land'])
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