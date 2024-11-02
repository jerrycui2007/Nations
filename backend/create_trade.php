<?php
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

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if user has enough of the resource
    $stmt = $pdo->prepare("SELECT `$resource` FROM commodities WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_resources = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!isset($user_resources[$resource]) || $user_resources[$resource] < $amount) {
        throw new Exception("You don't have enough " . str_replace('_', ' ', $resource));
    }

    // Create the trade offer
    $stmt = $pdo->prepare("INSERT INTO trades (seller_id, resource_offered, amount_offered, price_per_unit, date) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $resource, $amount, $price]);

    // Subtract the resources from the user's inventory
    $stmt = $pdo->prepare("UPDATE commodities SET `$resource` = `$resource` - ? WHERE id = ?");
    $stmt->execute([$amount, $user_id]);

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Trade offer created successfully'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
