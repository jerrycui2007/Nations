<?php
session_start();
require_once 'db_connection.php';
require_once 'equipment_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Equipment type not specified']);
    exit();
}

$equipment_type = $_GET['type'];

// Fetch unequipped equipment of the specified type
$stmt = $pdo->prepare("
    SELECT e.*, eb.buff_type, eb.value, b.description as buff_description
    FROM equipment e
    LEFT JOIN equipment_buffs eb ON e.equipment_id = eb.equipment_id
    LEFT JOIN buffs b ON eb.value = b.buff_id
    WHERE e.user_id = ? 
    AND e.type = ?
    AND NOT EXISTS (
        SELECT 1 FROM units u 
        WHERE (u.equipment_1_id = e.equipment_id 
            OR u.equipment_2_id = e.equipment_id 
            OR u.equipment_3_id = e.equipment_id 
            OR u.equipment_4_id = e.equipment_id)
    )
");

$stmt->execute([$_SESSION['user_id'], $equipment_type]);
$equipment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group equipment buffs
$equipment = [];
foreach ($equipment_data as $row) {
    if (!isset($equipment[$row['equipment_id']])) {
        $equipment[$row['equipment_id']] = [
            'equipment_id' => $row['equipment_id'],
            'name' => $row['name'],
            'rarity' => $row['rarity'],
            'type' => $row['type'],
            'is_foil' => $row['is_foil'],
            'buffs' => []
        ];
    }
    if ($row['buff_type']) {
        $equipment[$row['equipment_id']]['buffs'][] = [
            'buff_type' => $row['buff_type'],
            'value' => $row['value'],
            'description' => $row['buff_description']
        ];
    }
}

echo json_encode(array_values($equipment)); 