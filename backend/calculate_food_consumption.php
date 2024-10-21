<?php

function calculateFoodConsumption($user) {
    $population = $user['population'];
    $food = $user['food'];
    
    // Calculate food consumption (1 for every 1000 population, rounded)
    $food_consumption = round($population / 1000);

    // Ensure food doesn't go negative
    $new_food = max(0, $food - $food_consumption);
    $actual_consumption = $food - $new_food;

    return [
        'consumption' => $food_consumption,
        'actual_consumption' => $actual_consumption,
        'current_food' => $food,
        'new_food' => $new_food,
        'message' => "Consuming {$actual_consumption} food based on population of {$population}."
    ];
}
