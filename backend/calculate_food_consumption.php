<?php

include 'unit_config.php';

function calculateFoodConsumption($user) {
    global $pdo, $UNIT_CONFIG;
    
    $population = $user['population'];
    $food = $user['food'];
    
    // Calculate base food consumption (1 for every 5000 population, rounded)
    $base_consumption = round($population / 5000);
    
    // Get all units belonging to this user, including division mobilization state
    $stmt = $pdo->prepare("
        SELECT u.*, d.in_combat, d.mobilization_state 
        FROM units u 
        LEFT JOIN divisions d ON u.division_id = d.division_id 
        WHERE u.player_id = ?
    ");
    $stmt->execute([$user['id']]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate unit upkeep only for units in mobilized divisions
    $unit_food_consumption = 0;
    foreach ($units as $unit) {
        // Skip units that aren't in mobilized divisions
        if ($unit['division_id'] != 0 && $unit['mobilization_state'] !== 'mobilized') {
            continue;
        }
        
        $unit_type = strtolower(str_replace(' ', '_', $unit['type']));
        if (isset($UNIT_CONFIG[$unit_type]['upkeep']['food'])) {
            $unit_food_consumption += $UNIT_CONFIG[$unit_type]['upkeep']['food'];
        }
    }
    
    // Total consumption is base + unit upkeep
    $total_consumption = $base_consumption + $unit_food_consumption;
    
    // Ensure food doesn't go negative
    $new_food = max(0, $food - $total_consumption);
    $actual_consumption = $food - $new_food;
    
    return [
        'base_consumption' => $base_consumption,
        'unit_consumption' => $unit_food_consumption,
        'total_consumption' => $total_consumption,
        'actual_consumption' => $actual_consumption,
        'current_food' => $food,
        'new_food' => $new_food,
        'message' => "Consuming {$actual_consumption} food ({$base_consumption} from population, {$unit_food_consumption} from units)"
    ];
}
