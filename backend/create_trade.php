<?php
global $conn;
session_start();
require_once 'db_connection.php';
require_once 'resource_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$resource = $_POST['resource'] ?? '';
$amount = intval($_POST['amount'] ?? 0);
$price = floatval($_POST['price'] ?? 0);

if (empty($resource) || $amount <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input parameters']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if user has enough of the resource
    $stmt = $conn->prepare("SELECT `$resource` FROM commodities WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_resources = $result->fetch_assoc();

    if (!isset($user_resources[$resource]) || $user_resources[$resource] < $amount) {
        throw new Exception("You don't have enough " . str_replace('_', ' ', $resource));
    }

    // Create the trade offer
    $stmt = $conn->prepare("INSERT INTO trades (seller_id, resource_offered, amount_offered, price_per_unit, date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isid", $user_id, $resource, $amount, $price);
    $stmt->execute();

    // Subtract the resources from the user's inventory
    $stmt = $conn->prepare("UPDATE commodities SET `$resource` = `$resource` - ? WHERE id = ?");
    $stmt->bind_param("ii", $amount, $user_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Trade offer created successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
