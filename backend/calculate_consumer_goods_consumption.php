<?php

function calculateConsumerGoodsConsumption($user) {
    $population = $user['population'];
    $consumer_goods = $user['consumer_goods'];
    
    // Calculate consumer goods consumption (1 for every 5000 population, rounded)
    $consumer_goods_consumption = round($population / 5000);

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
