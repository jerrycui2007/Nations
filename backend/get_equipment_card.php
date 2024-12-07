<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$item = json_decode(file_get_contents('php://input'), true);
if (!$item) {
    exit('Invalid data');
}

ob_start();
include '../frontend/components/equipment_card.php';
$html = ob_get_clean();

echo $html; 