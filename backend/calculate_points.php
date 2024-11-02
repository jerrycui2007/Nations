<?php
require_once 'db_connection.php';  // Updated path
require_once 'factory_config.php';

function calculatePoints($user_id) {
    global $pdo, $FACTORY_CONFIG;

    try {
        // Fetch user data and calculate total land
        $stmt = $pdo->prepare("
            SELECT u.population, 
                   SUM(l.cleared_land + l.urban_areas + l.forest + l.mountain + 
                       l.river + l.lake + l.grassland + l.jungle + l.desert + l.tundra) AS total_land
            FROM users u
            JOIN land l ON u.id = l.id
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate points from population (divided by 1000, rounded to nearest whole number)
        $population_points = round($user_data['population'] / 1000);
        
        // Calculate points from total land
        $land_points = $user_data['total_land'];

        // Initialize total points
        $points = $population_points + $land_points;

        // Fetch user's factories
        $stmt = $pdo->prepare("SELECT * FROM factories WHERE id = ?");
        $stmt->execute([$user_id]);
        $factories = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate points from factories
        foreach ($FACTORY_CONFIG as $factory_type => $factory_data) {
            if (isset($factories[$factory_type])) {
                $points += $factories[$factory_type] * $factory_data['gp_value'];
            }
        }

        // Update user's points
        $stmt = $pdo->prepare("UPDATE users SET gp = ? WHERE id = ?");
        $stmt->execute([$points, $user_id]);

        return $points;
    } catch (PDOException $e) {
        error_log("Error calculating points: " . $e->getMessage());
        return false;
    }
}

function getPointsForUser($user_id) {
    return calculatePoints($user_id);
}
?>
