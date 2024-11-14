<?php

function calculateConsumerGoodsConsumption($user) {
    $population = $user['population'];
    $consumer_goods = $user['consumer_goods'];
    
     // Only consume consumer goods if population is >= 75000 (tier > 1)
     if ($population >= 75000) {
        // Calculate consumer goods consumption (1 for every 10000 population, rounded)
        $consumer_goods_consumption = round($population / 10000);
    } else {
        $consumer_goods_consumption = 0;
    }

    // Ensure consumer goods doesn't go negative
    $new_consumer_goods = max(0, $consumer_goods - $consumer_goods_consumption);
    $actual_consumption = $consumer_goods - $new_consumer_goods;

    return [
        'consumption' => $consumer_goods_consumption,
        'actual_consumption' => $actual_consumption,
        'current_consumer_goods' => $consumer_goods,
        'new_consumer_goods' => $new_consumer_goods,
        'message' => "Consuming {$actual_consumption} consumer goods based on population of {$population}."
    ];
}
