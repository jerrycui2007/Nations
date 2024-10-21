<?php

function calculateIncome($user) {
    $population = $user['population'];
    $food = $user['food'];
    $power = $user['power'];
    $current_money = $user['money'];
    $consumer_goods = $user['consumer_goods'];

    // Calculate money increase (1 for every 100 population, rounded to nearest whole number)
    $money_increase = round($population / 100);

    // Check if food, power, and consumer goods are greater than 0
    if ($food > 0 && $power > 0 && $consumer_goods > 0) {
        $new_money = $current_money + $money_increase;
        return [
            'success' => true,
            'new_money' => $new_money,
            'increase' => $money_increase,
            'message' => "Added {$money_increase} money based on population of {$population}."
        ];
    } else {
        return [
            'success' => false,
            'new_money' => $current_money,
            'increase' => 0,
            'message' => "No money added due to insufficient food, power, or consumer goods."
        ];
    }
}
