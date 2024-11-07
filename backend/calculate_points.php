<?php
require_once 'db_connection.php';  // Updated path
require_once 'factory_config.php';

function getUserData($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT u.population, 
               (l.cleared_land + l.urban_areas + l.forest + l.mountain + l.used_land + 
                l.river + l.lake + l.grassland + l.jungle + l.desert + l.tundra) AS total_land
        FROM users u
        JOIN land l ON u.id = l.id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getFactoryData($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM factories WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getBuildingData($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM buildings WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function calculatePopulationPoints($population) {
    $population_points = round($population / 1000);
    error_log("Population points: $population_points");
    return $population_points;
}

function calculateLandPoints($total_land) {
    $land_points = $total_land ?? 0;
    error_log("Land points: $land_points (Total land: {$total_land})");
    return $land_points;
}

function calculateFactoryPoints($factories, $FACTORY_CONFIG) {
    $factory_points = 0;
    foreach ($FACTORY_CONFIG as $factory_type => $factory_data) {
        if (isset($factories[$factory_type]) && $factories[$factory_type] > 0) {
            $factory_value = $factories[$factory_type] * $factory_data['gp_value'];
            $factory_points += $factory_value;
            error_log("Factory $factory_type adds $factory_value GP");
        }
    }
    error_log("Total factory points: $factory_points");
    return $factory_points;
}

function calculateBuildingPoints($buildings) {
    $building_points = 0;
    foreach ($buildings as $building => $level) {
        if ($level > 0 && $building != 'id') {
            $building_points += $level;
            error_log("Building $building level $level adds $level GP");
        }
    }
    error_log("Total building points: $building_points");
    return $building_points;
}

function calculatePoints($user_id) {
    global $pdo, $FACTORY_CONFIG;

    try {
        // Get the GP breakdown instead of calculating directly
        $gp_breakdown = get_gp_breakdown($user_id);
        if (!$gp_breakdown) {
            error_log("No GP breakdown found for ID: $user_id");
            return false;
        }

        $points = $gp_breakdown['total_gp'];

        // Update user's points
        $stmt = $pdo->prepare("UPDATE users SET gp = ? WHERE id = ?");
        $stmt->execute([$points, $user_id]);

        error_log("User $user_id - Final GP calculation: $points");
        return $points;

    } catch (PDOException $e) {
        error_log("Error calculating points for user $user_id: " . $e->getMessage());
        return false;
    }
}

function getPointsForUser($user_id) {
    return calculateTotalPoints($user_id);
}
?>
