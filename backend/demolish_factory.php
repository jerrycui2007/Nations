<?php
require_once 'db_connection.php';
require_once 'resource_config.php';
require_once 'factory_config.php';
session_start();

try {
    $user_id = $_SESSION['user_id'];
    $factory_type = $_POST['factory_type'];
    
    if (!isset($FACTORY_CONFIG[$factory_type])) {
        throw new Exception("Invalid factory type");
    }

    $factory_data = $FACTORY_CONFIG[$factory_type];
    
    $pdo->beginTransaction();
    
    // Check if user has this factory type
    $stmt = $pdo->prepare("SELECT $factory_type FROM factories WHERE id = ? AND $factory_type > 0");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        throw new Exception("You don't have any factories of this type");
    }
    
    // Decrease factory count
    $stmt = $pdo->prepare("UPDATE factories SET $factory_type = $factory_type - 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    
    // Return land
    $land_type = $factory_data['land']['type'];
    $land_amount = $factory_data['land']['amount'];
    
    $stmt = $pdo->prepare("UPDATE land SET 
        $land_type = $land_type + ?,
        used_land = used_land - ?
        WHERE id = ?");
    $stmt->execute([$land_amount, $land_amount, $user_id]);
    
    // Refund 50% of resources
    foreach ($factory_data['construction_cost'] as $cost) {
        $refund_amount = floor($cost['amount'] * 0.5); // 50% refund
        $resource = $cost['resource'];
        
        $stmt = $pdo->prepare("UPDATE commodities SET $resource = $resource + ? WHERE id = ?");
        $stmt->execute([$refund_amount, $user_id]);
    }
    
    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => "Factory demolished successfully. Resources partially refunded."
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}