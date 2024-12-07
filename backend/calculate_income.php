<?php

include 'unit_config.php';

function calculateIncome($user) {
    global $pdo, $UNIT_CONFIG;
    
    $population = $user['population'];
    $food = $user['food'];
    $power = $user['power'];
    $current_money = $user['money'];
    $consumer_goods = $user['consumer_goods'];

    // Calculate base money increase (1 for every 30 population, rounded to nearest whole number)
    $money_increase = round($population / 30);

    // Get all units belonging to this user, including division mobilization state
    $stmt = $pdo->prepare("
        SELECT u.*, d.in_combat, d.mobilization_state 
        FROM units u 
        LEFT JOIN divisions d ON u.division_id = d.division_id 
        WHERE u.player_id = ?
    ");
    $stmt->execute([$user['id']]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total unit upkeep only for units in mobilized divisions
    $unit_money_upkeep = 0;
    foreach ($units as $unit) {
        // Skip units that aren't in mobilized divisions
        if ($unit['division_id'] != 0 && $unit['mobilization_state'] !== 'mobilized') {
            continue;
        }
        
        // Include upkeep for units in reserves (division_id = 0) or mobilized divisions
        $unit_type = strtolower(str_replace(' ', '_', $unit['type']));
        if (isset($UNIT_CONFIG[$unit_type]['upkeep']['money'])) {
            $unit_money_upkeep += $UNIT_CONFIG[$unit_type]['upkeep']['money'];
        }
    }
    $net_income = $money_increase - $unit_money_upkeep;

    // Only check consumer goods if population >= 75000 (tier > 1)
    $consumer_goods_check = $population < 75000 || $consumer_goods > 0;

    // Check if food, power, and consumer goods (if needed) are greater than 0
    if ($food > 0 && $power > 0 && $consumer_goods_check) {
        $new_money = $current_money + $net_income;
        return [
            'success' => true,
            'new_money' => $new_money,
            'increase' => $net_income,
            'message' => "Added {$net_income} money (Income: {$money_increase}, Upkeep: {$unit_money_upkeep}) based on population of {$population}."
        ];
    } else {
        return [
            'success' => false,
            'new_money' => $current_money,
            'increase' => 0,
            'message' => "No money added due to insufficient resources."
        ];
    }
}
