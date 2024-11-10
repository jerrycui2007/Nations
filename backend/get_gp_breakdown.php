<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("GP breakdown request started");

header('Content-Type: application/json');
session_start();

require_once 'db_connection.php';
require_once 'calculate_points.php';

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    error_log("Processing GP breakdown for user_id: $user_id");
    
    // Get user data
    $stmt = $pdo->prepare("
        SELECT population, tier 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("User data fetched: " . print_r($user, true));

    if (!$user) {
        error_log("User not found in database");
        throw new Exception("User not found");
    }

    // Get land data
    error_log("Executing land query");
    $stmt = $pdo->prepare("
        SELECT SUM(cleared_land + forest + mountains + plains + urban_areas) as total_land 
        FROM land 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $land = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Land data fetched: " . print_r($land, true));

    // Get factory GP
    $stmt = $pdo->prepare("
        SELECT SUM(gp_value) as factory_gp 
        FROM factories 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $factory = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get building GP
    $stmt = $pdo->prepare("
        SELECT SUM(gp_value) as building_gp 
        FROM buildings 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $building = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate GP components
    $population_gp = floor($user['population'] / 1000);
    $land_gp = $land['total_land'] ?? 0;
    $factory_gp = $factory['factory_gp'] ?? 0;
    $building_gp = $building['building_gp'] ?? 0;
    $total_gp = $population_gp + $land_gp + $factory_gp + $building_gp;

    // Prepare response
    $response = [
        'population_gp' => $population_gp,
        'land_gp' => $land_gp,
        'factory_gp' => $factory_gp,
        'building_gp' => $building_gp,
        'total_gp' => $total_gp
    ];
    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_gp_breakdown.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'error' => 'An error occurred while calculating GP breakdown'
    ]);
}