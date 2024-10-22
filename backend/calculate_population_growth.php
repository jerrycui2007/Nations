<?php

function calculatePopulationGrowth($user) {
    $population = $user['population'];
    $food = $user['food'];
    $power = $user['power'];
    $consumer_goods = $user['consumer_goods'];

    $growth_rate = 0.01; // 1% growth rate
    $growth = 0;

    if ($food > 0 && $power > 0 && $consumer_goods > 0) {
        // Population increases by 1%
        $growth = round($population * $growth_rate);
    } elseif ($food == 0) {
        // Population decreases by 1%
        $growth = -round($population * $growth_rate);
    }

    $new_population = max(0, $population + $growth);

    return [
        'old_population' => $population,
        'new_population' => $new_population,
        'growth' => $growth,
        'message' => "Population changed by {$growth} based on current conditions."
    ];
}
