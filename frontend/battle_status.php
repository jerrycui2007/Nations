<?php
session_start();
require_once '../backend/db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['battle_id'])) {
    http_response_code(400);
    exit();
}

$battle_id = intval($_GET['battle_id']);

// Fetch battle status
$stmt = $pdo->prepare("
    SELECT is_over, winner_name 
    FROM battles 
    WHERE battle_id = ?
");
$stmt->execute([$battle_id]);
$battle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$battle) {
    http_response_code(404);
    exit();
}

header('Content-Type: application/json');
echo json_encode([
    'is_over' => (bool)$battle['is_over'],
    'winner_name' => $battle['winner_name']
]); 