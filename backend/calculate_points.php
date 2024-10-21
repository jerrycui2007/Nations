<?php
function calculatePoints($population, $total_land) {
    $population_points = round($population / 1000);
    $land_points = round($total_land);
    return $population_points + $land_points;
}

function getPointsForUser($conn, $user_id) {
    $stmt = $conn->prepare("SELECT users.population, SUM(land.cleared_land + land.urban_areas + land.forest + land.mountain + land.river + land.lake + land.grassland + land.jungle + land.desert + land.tundra) AS total_land FROM users JOIN land ON users.id = land.id WHERE users.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $population = $row['population'];
        $total_land = $row['total_land'];
        $points = calculatePoints($population, $total_land);
        return $points;
    }
    
    return 0; // Return 0 if user not found or population is 0
}
?>
