<?php
session_start();
require_once 'db_connection.php';
require_once 'gp_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $gp_data = calculateTotalGP($pdo, $user_id);
    
    if (!is_array($gp_data)) {
        throw new Exception('Invalid GP data returned');
    }
    
    echo json_encode($gp_data);
} catch (Exception $e) {
    error_log("GP Breakdown Error: " . $e->getMessage());
    echo json_encode([
        'error' => 'An error occurred while calculating GP breakdown: ' . $e->getMessage()
    ]);
}