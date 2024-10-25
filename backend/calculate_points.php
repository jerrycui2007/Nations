<?php
require_once 'db_connection.php';
require_once 'factory_config.php';

function calculatePoints($user_id) {
    global $conn, $FACTORY_CONFIG;

    // Fetch user data and calculate total land
    $stmt = $conn->prepare("
        SELECT u.population, 
               SUM(l.cleared_land + l.urban_areas + l.forest + l.mountain + l.river + l.lake + l.grassland + l.jungle + l.desert + l.tundra) AS total_land
        FROM users u
        JOIN land l ON u.id = l.id
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    // Calculate points from population (divided by 1000, rounded to nearest whole number)
    $population_points = round($user_data['population'] / 1000);
    
    // Calculate points from total land
    $land_points = $user_data['total_land'];

    // Initialize total points
    $points = $population_points + $land_points;

    // Fetch user's factories
    $stmt = $conn->prepare("SELECT * FROM factories WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $factories = $result->fetch_assoc();

    // Calculate points from factories
    foreach ($FACTORY_CONFIG as $factory_type => $factory_data) {
        if (isset($factories[$factory_type])) {
            $points += $factories[$factory_type] * $factory_data['gp_value'];
        }
    }

    // Update user's points
    $stmt = $conn->prepare("UPDATE users SET gp = ? WHERE id = ?");
    $stmt->bind_param("ii", $points, $user_id);
    $stmt->execute();

    return $points;
}

function getPointsForUser($conn, $user_id) {
    return calculatePoints($user_id);
}
?>
