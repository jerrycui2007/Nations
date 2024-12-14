<?php
session_start();
require_once 'db_connection.php';
require_once 'equipment_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['type']) || !isset($_GET['unit_level'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Equipment type or unit level not specified']);
    exit();
}

$equipment_type = $_GET['type'];
$unit_level = (int)$_GET['unit_level'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$offset = ($page - 1) * $per_page;

// Define rarity order
$rarity_order = "FIELD(e.rarity, 'legendary', 'epic', 'rare', 'uncommon', 'common')";

// Build rarity restriction based on unit level
$rarity_restriction = "AND (
    (e.rarity = 'legendary' AND ? >= 15) OR
    (e.rarity = 'epic' AND ? >= 10) OR
    (e.rarity = 'rare' AND ? >= 5) OR
    e.rarity IN ('uncommon', 'common')
)";

// First, get total count of available equipment
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM equipment e
    WHERE e.user_id = ? 
    AND e.type = ?
    " . $rarity_restriction . "
    AND NOT EXISTS (
        SELECT 1 FROM units u 
        WHERE (u.equipment_1_id = e.equipment_id 
            OR u.equipment_2_id = e.equipment_id 
            OR u.equipment_3_id = e.equipment_id 
            OR u.equipment_4_id = e.equipment_id)
    )
");

$count_stmt->execute([
    $_SESSION['user_id'], 
    $equipment_type, 
    $unit_level, 
    $unit_level, 
    $unit_level
]);
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch unequipped equipment of the specified type with pagination and sorting
$stmt = $pdo->prepare("
    SELECT e.*, eb.buff_type, eb.value, b.description as buff_description
    FROM equipment e
    LEFT JOIN equipment_buffs eb ON e.equipment_id = eb.equipment_id
    LEFT JOIN buffs b ON eb.value = b.buff_id
    WHERE e.user_id = ? 
    AND e.type = ?
    " . $rarity_restriction . "
    AND NOT EXISTS (
        SELECT 1 FROM units u 
        WHERE (u.equipment_1_id = e.equipment_id 
            OR u.equipment_2_id = e.equipment_id 
            OR u.equipment_3_id = e.equipment_id 
            OR u.equipment_4_id = e.equipment_id)
    )
    ORDER BY " . $rarity_order . ", e.is_foil DESC, e.name ASC
    LIMIT ? OFFSET ?
");

// Bind parameters
$stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(2, $equipment_type, PDO::PARAM_STR);
$stmt->bindValue(3, $unit_level, PDO::PARAM_INT);
$stmt->bindValue(4, $unit_level, PDO::PARAM_INT);
$stmt->bindValue(5, $unit_level, PDO::PARAM_INT);
$stmt->bindValue(6, $per_page, PDO::PARAM_INT);
$stmt->bindValue(7, $offset, PDO::PARAM_INT);

$stmt->execute();
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

// Calculate if there are more items to load
$has_more = ($offset + $per_page) < $total_count;

echo json_encode([
    'equipment' => array_values($equipment),
    'has_more' => $has_more,
    'total_count' => $total_count,
    'current_page' => $page,
    'per_page' => $per_page
]);
