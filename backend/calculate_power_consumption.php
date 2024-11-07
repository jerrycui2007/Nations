<?php

function calculatePowerConsumption($user) {
    $population = $user['population'];
    $power = $user['power'];
    
    // Calculate power consumption (1 for every 10000 population, rounded)
    $power_consumption = round($population / 10000);

    // Ensure power doesn't go negative
    $new_power = max(0, $power - $power_consumption);
    $actual_consumption = $power - $new_power;

    return [
        'consumption' => $power_consumption,
        'actual_consumption' => $actual_consumption,
        'current_power' => $power,
        'new_power' => $new_power,
        'message' => "Consuming {$actual_consumption} power based on population of {$population}."
    ];
}
