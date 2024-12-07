<?php

$MINIMUM_LEVEL_CONFIG = [
    "Common" => 1,
    "Uncommon" => 1,
    "Rare" => 5, 
    "Epic" => 10,
    "Legendary" => 15
];

$TYPE_CONFIG = [
    "Infantry" => [
        1 => "Infantry Accessory",
        2 => "Body Armour",
        3 => "Infantry Weapon",
        4 => "Battle Juice",
    ],
    "Armour" => [
        1 => "Heavy Accessory",
        2 => "Crew",
        3 => "Engine",
        4 => "Battle Juice",
    ],
    "Air" => [
        1 => "Ammunition",
        2 => "Crew",
        3 => "Engine",
        4 => "Battle Juice",
    ],
    "Static" => [
        1 => "Ammunition",
        2 => "Crew",
        3 => "Heavy Accessory",
        4 => "Battle Juice"
    ],
    "Special Forces" => [
        1 => "Infantry Accessory",
        2 => "Body Armour",
        3 => "Infantry Weapon",
        4 => "Battle Juice",
    ],
];

$PROBABILITY_CONFIG = [
    "Regular" => [
            "Common" => 750,
            "Uncommon" => 150,
            "Rare" => 75,
            "Epic" => 24,
            "Legendary" => 1
    ],
    "Rare" => [
        "Common" => 0,
        "Uncommon" => 0,
        "Rare" => 75,
        "Epic" => 24,
        "Legendary" => 1
    ],
    "Epic" => [
        "Common" => 0,
        "Uncommon" => 0,
        "Rare" => 0,
        "Epic" => 24,
        "Legendary" => 1
    ]
    ];

$CRATE_CONFIG = [
    "Small" => [
        "cost" => 1,
        "description" => "1 piece of equipment.",
        "contents" => ["Regular"],
    ],
    "Medium" => [
        "cost" => 20,
        "description" => "3 pieces of equipment, 2 guarenteed rares or higher.",
        "contents" => ["Rare", "Rare", "Regular"],
    ],
    "Epic" => [
        "cost" => 50,
        "description" => "3 pieces of equipment, 1 guarenteed epic or higher, 2 guarenteed rares or higher.",
        "contents" => ["Epic", "Rare", "Rare"],
    ]
];

$STAT_TOTAL_CONFIG = [
    "Common" => 1,
    "Uncommon" => 2,
    "Rare" => 4,
    "Epic" => 7,
    "Legendary" => 12
];

$POSSIBLE_BUFF_CONFIG = [
    "FirepowerMultiplierAgainstUnit" => ["Infantry", "Armour", "Air", "Static", "Special Forces"],
    "AllStatsMultiplier" => ["westberg", "amarino", "san_sebastian", "tind", "zaheria"]
];

$POSSIBLE_BATTLE_JUICE_CONFIG = [
    "FirepowerMultiplier", "ArmourMultiplier", "ManeuverMultiplier", "HealthMultiplier"
];

$BATTLE_JUICE_CONFIG = [
    "Common" => 25,
    "Uncommon" => 50,
    "Rare" => 100,
    "Epic" => 150,
    "Legendary" => 200,
    "CommonFoil" => 150,
    "UncommonFoil" => 200,
    "RareFoil" => 300,
    "EpicFoil" => 400,
    "LegendaryFoil" => 500,
];