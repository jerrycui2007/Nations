<?php
session_start();
require_once 'db_connection.php';
require_once 'calculate_points.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $user_data = getUserData($pdo, $user_id);
    $factory_data = getFactoryData($pdo, $user_id);
    $building_data = getBuildingData($pdo, $user_id);

    $population_gp = calculatePopulationPoints($user_data['population']);
    $land_gp = calculateLandPoints($user_data['total_land']);
    $factory_gp = calculateFactoryPoints($factory_data, $FACTORY_CONFIG);
    $building_gp = calculateBuildingPoints($building_data);
    $total_gp = $population_gp + $land_gp + $factory_gp + $building_gp;

    echo json_encode([
        'population_gp' => $population_gp,
        'land_gp' => $land_gp,
        'factory_gp' => $factory_gp,
        'building_gp' => $building_gp,
        'total_gp' => $total_gp
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}