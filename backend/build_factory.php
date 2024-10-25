<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$factory_type = $_POST['factory_type'];

// Start transaction
$conn->begin_transaction();

try {
    // Define factory costs and land requirements
    $factory_data = [
        'farm' => ['cost' => 500, 'land' => 5, 'land_type' => 'cleared_land'],
        'windmill' => ['cost' => 250, 'land' => 5, 'land_type' => 'cleared_land'],
        'quarry' => ['cost' => 1000, 'land' => 5, 'land_type' => 'mountain'],
        'sandstone_quarry' => ['cost' => 1000, 'land' => 5, 'land_type' => 'desert'],
        'sawmill' => ['cost' => 1000, 'land' => 5, 'land_type' => 'forest'],
        'automobile_factory' => ['cost' => 5000, 'land' => 5, 'land_type' => 'cleared_land', 'building_materials' => 1000, 'metal' => 100]
    ];

    if (!isset($factory_data[$factory_type])) {
        throw new Exception("Invalid factory type");
    }

    $cost = $factory_data[$factory_type]['cost'];
    $land_required = $factory_data[$factory_type]['land'];
    $land_type = $factory_data[$factory_type]['land_type'];

    // Check if user has enough money
    $stmt = $conn->prepare("SELECT Money FROM commodities WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_money = $result->fetch_assoc()['Money'];

    if ($user_money < $cost) {
        throw new Exception("Not enough money to build the factory");
    }

    // Check if user has enough land
    $stmt = $conn->prepare("SELECT $land_type, used_land FROM land WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $land_data = $result->fetch_assoc();

    if ($land_data[$land_type] < $land_required) {
        throw new Exception("Not enough $land_type to build the factory");
    }

    // For automobile factory, check additional resources
    if ($factory_type === 'automobile_factory') {
        $stmt = $conn->prepare("SELECT Building_Materials, Metal FROM commodities WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $resources = $result->fetch_assoc();

        if ($resources['Building_Materials'] < $factory_data[$factory_type]['building_materials'] || 
            $resources['Metal'] < $factory_data[$factory_type]['metal']) {
            throw new Exception("Not enough resources to build the automobile factory");
        }
    }

    // Update user's money
    $stmt = $conn->prepare("UPDATE commodities SET Money = Money - ? WHERE id = ?");
    $stmt->bind_param("di", $cost, $user_id);
    $stmt->execute();

    // Update user's land
    $stmt = $conn->prepare("UPDATE land SET $land_type = $land_type - ?, used_land = used_land + ? WHERE id = ?");
    $stmt->bind_param("iii", $land_required, $land_required, $user_id);
    $stmt->execute();

    // Update user's resources for automobile factory
    if ($factory_type === 'automobile_factory') {
        $stmt = $conn->prepare("UPDATE commodities SET Building_Materials = Building_Materials - ?, Metal = Metal - ? WHERE id = ?");
        $stmt->bind_param("iii", $factory_data[$factory_type]['building_materials'], $factory_data[$factory_type]['metal'], $user_id);
        $stmt->execute();
    }

    // Instead of increasing factory count, add to construction queue
    $construction_time = 30; 
    $stmt = $conn->prepare("INSERT INTO factory_queue (id, factory_type, minutes_left) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $factory_type, $construction_time);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Successfully started construction of a new $factory_type"
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();