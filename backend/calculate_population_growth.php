<?php

function calculatePopulationGrowth($user) {
    $population = $user['population'];
    $food = $user['food'];
    $power = $user['power'];
    $consumer_goods = $user['consumer_goods'];
    $urban_areas = $user['urban_areas'];

    $growth_rate = 0.005; // 0.5% growth rate
    $growth = 0;

    // Only check consumer goods if population >= 75000 (tier > 1)
    $consumer_goods_check = $population < 75000 || $consumer_goods > 0;

    if ($food > 0 && $power > 0 && $consumer_goods_check) {
        // Population increases 
        $growth = round($population * $growth_rate);
    } elseif ($food == 0) {
        // Population decreases
        $growth = -round($population * $growth_rate);
    }

    $new_population = max(0, $population + $growth);

    // Cap the population based on the number of urban areas
    $max_population_by_urban_areas = $urban_areas * 1000;
    $max_population = min(599999, $max_population_by_urban_areas); // Cap at 249,999, because haven't added tier 2 yet

    if ($new_population > $max_population) {
        $new_population = $max_population;
        $growth = $new_population - $population; // Adjust growth to reflect the cap
    }

    return [
        'old_population' => $population,
        'new_population' => $new_population,
        'growth' => $growth,
        'message' => "Population changed by {$growth} based on current conditions."
    ];
}
