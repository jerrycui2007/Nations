<?php

$FACTORY_CONFIG = [
    'farm' => [
        'tier' => 1,
        'name' => 'Farm',
        'input' => [['resource' => 'money', 'amount' => 7]],
        'output' => [['resource' => 'food', 'amount' => 1]],
        'construction_cost' => [['resource' => 'money', 'amount' => 500]],
        'land' => ['type' => 'cleared_land', 'amount' => 5],
        'construction_time' => 30,
        'gp_value' => 1
    ],
    'windmill' => [
        'tier' => 1,
        'name' => 'Windmill',
        'input' => [['resource' => 'money', 'amount' => 2]],
        'output' => [['resource' => 'power', 'amount' => 1]],
        'construction_cost' => [['resource' => 'money', 'amount' => 250]],
        'land' => ['type' => 'cleared_land', 'amount' => 5],
        'construction_time' => 30,
        'gp_value' => 1
    ],
    'quarry' => [
        'tier' => 1,
        'name' => 'Quarry',
        'input' => [['resource' => 'money', 'amount' => 7]],
        'output' => [['resource' => 'building_materials', 'amount' => 1]],
        'construction_cost' => [['resource' => 'money', 'amount' => 1000]],
        'land' => ['type' => 'mountain', 'amount' => 5],
        'construction_time' => 30,
        'gp_value' => 1
    ],
    'sandstone_quarry' => [
        'tier' => 1,
        'name' => 'Sandstone Quarry',
        'input' => [['resource' => 'money', 'amount' => 7]],
        'output' => [['resource' => 'building_materials', 'amount' => 1]],
        'construction_cost' => [['resource' => 'money', 'amount' => 1000]],
        'land' => ['type' => 'desert', 'amount' => 5],
        'construction_time' => 30,
        'gp_value' => 1
    ],
    'sawmill' => [
        'tier' => 1,
        'name' => 'Sawmill',
        'input' => [['resource' => 'money', 'amount' => 7]],
        'output' => [['resource' => 'building_materials', 'amount' => 1]],
        'construction_cost' => [['resource' => 'money', 'amount' => 1000]],
        'land' => ['type' => 'forest', 'amount' => 5],
        'construction_time' => 30,
        'gp_value' => 1
    ],
    'automobile_factory' => [
        'tier' => 1,
        'name' => 'Automobile Factory',
        'input' => [
            ['resource' => 'money', 'amount' => 12],
            ['resource' => 'power', 'amount' => 10],
            ['resource' => 'metal', 'amount' => 1]
        ],
        'output' => [['resource' => 'consumer_goods', 'amount' => 6]],
        'construction_cost' => [
            ['resource' => 'money', 'amount' => 5000],
            ['resource' => 'building_materials', 'amount' => 1000],
            ['resource' => 'metal', 'amount' => 100]
        ],
        'land' => ['type' => 'cleared_land', 'amount' => 5],
        'construction_time' => 30,
        'gp_value' => 1
    ]
];