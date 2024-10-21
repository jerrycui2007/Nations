<?php
function calculatePoints($population) {
    return round($population / 1000);
}

function getPointsForUser($conn, $user_id) {
    $stmt = $conn->prepare("SELECT population FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $population = $row['population'];
        $points = calculatePoints($population);
        return $points;
    }
    
    return 0; // Return 0 if user not found or population is 0
}
?>
