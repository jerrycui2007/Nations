<?php

global $MISSION_CONFIG;

$MISSION_CONFIG = [
    "riot_control" => [
        "name" => "Riot Control",
        "description" => "A Westberg member of the United Nations is experiencing serious riot problems after a somewhat shady election. As a fellow UN member, it is our duty to help them.",
        "continent" => "westberg",
        "spawn_weight" => 1000,
        "rarity" => "Common",
        "defender_division_name" => "Westberg Rioters",
        "rewards" => [
            ["resource" => "money", "amount" => 1000]
        ],
        "enemies" => [
            "rioter" => [
                "weight" => 100,
                "min_amount" => 10,
                "max_amount" => 15
            ]
        ]
    ],
    "jungle_fever" => [
        "name" => "Jungle Fever",
        "description" => "The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.",
        "continent" => "amarino",
        "spawn_weight" => 1000,
        "rarity" => "Rare",
        "defender_division_name" => "Followers of Black Horn",
        "rewards" => [
            ["resource" => "money", "amount" => 8000]
        ],
        "enemies" => [
            "black_horn" => [
                "weight" => 100,
                "min_amount" => 10,
                "max_amount" => 15
            ]
        ]
    ],
    "its_the_least_we_can_do" => [
        "name" => "It's the Least We Can Do",
        "description" => "The Union of Nations is expecting us to contribute in the fight against the Bihadj Terrorists.",
        "continent" => "zaheria",
        "spawn_weight" => 1000,
        "rarity" => "Common",
        "defender_division_name" => "Bihadj Insurgents",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
            ["resource" => "ammunition", "amount" => 20]
        ],
        "enemies" => [
            "ak47_infantry" => [
                "weight" => 70,
                "min_amount" => 7,
                "max_amount" => 11
            ],
            "rpg_infantry" => [
                "weight" => 25,
                "min_amount" => 3,
                "max_amount" => 4
            ],
            "t72_tank" => [
                "weight" => 5,
                "min_amount" => 0,
                "max_amount" => 1
            ]
        ]
    ],
    "foreign_affairs" => [
        "name" => "Foreign Affairs",
        "description" => "Hey! Look over there, Mr. President! *Boom!* *Boom!* Take the cash and run, dammit!",
        "continent" => "westberg",
        "spawn_weight" => 1000,
        "rarity" => "Common",
        "defender_division_name" => "Secret Service",
        "rewards" => [
            ["resource" => "money", "amount" => 10000]
        ],
        "enemies" => [
            "secret_agent" => [
                "weight" => 100,
                "min_amount" => 10,
                "max_amount" => 15
            ]
        ]
    ],  
    "suppressing_the_khev_minosk" => [
        "name" => "Suppressing the Khev Minosk",
        "description" => "We need to stop the advancements of the terror group Khev Minosk. Strike with force.",
        "continent" => "zaheria",
        "spawn_weight" => 1000,
        "rarity" => "Common",
        "defender_division_name" => "Khev Minosk",
        "rewards" => [
            ["resource" => "money", "amount" => 2000]
        ],
        "enemies" => [
            "ak47_infantry" => [
                "weight" => 70,
                "min_amount" => 7,
                "max_amount" => 11
            ],
            "rpg_infantry" => [
                "weight" => 25,
                "min_amount" => 3,
                "max_amount" => 4
            ],
        ]
    ],
    "homeland_offence" => [
        "name" => "Homeland Offence",
        "description" => "To be frank, we don't like Oldenburg. It's an irrelevant little nation on the continent of Westberg, and it's mere existence is annoying. We should attack them a bit.",
        "continent" => "westberg",
        "spawn_weight" => 1000,
        "rarity" => "Common",
        "defender_division_name" => "Oldenburg Defence",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
            ["resource" => "cow", "amount" => 100]
        ],
        "enemies" => [
            "medic" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "national_guard" => [
                "weight" => 90,
                "min_amount" => 9,
                "max_amount" => 13
            ],
        ]
    ],
    "little_brother_wants_out" => [
        "name" => "Little Brother Wants Out",
        "description" => "Diplomatic negotiations over a small dependent region in San Sebastian had recently escalated into a armed conflict between state and regional rebels. As a fellow member of the United Nations, we are obliged to help.",
        "continent" => "san_sebastian",
        "spawn_weight" => 1000,
        "rarity" => "Rare",
        "defender_division_name" => "Tyrian Rebels",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
            ["resource" => "building_materials", "amount" => 250]
        ],
        "enemies" => [
            "medic" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "infantry" => [
                "weight" => 45,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "horseback_infantry" => [
                "weight" => 45,
                "min_amount" => 4,
                "max_amount" => 6
            ],
        ]
    ],
    "supply_raid" => [
        "name" => "Supply Raid",
        "description" => "Strategically attacking supply depots of Bihadj outposts can prove valuable.",
        "continent" => "zaheria",
        "spawn_weight" => 2500,
        "rarity" => "Uncommon",
        "defender_division_name" => "Bihadj Insurgents",
        "rewards" => [
            ["resource" => "metal", "amount" => 250],
            ["resource" => "building_materials", "amount" => 250],
            ["resource" => "fuel", "amount" => 50],
            ["resource" => "ammunition", "amount" => 100],
        ],
        "enemies" => [
            "ak47_infantry" => [
                "weight" => 65,
                "min_amount" => 7,
                "max_amount" => 9
            ],
            "rpg_infantry" => [
                "weight" => 25,
                "min_amount" => 3,
                "max_amount" => 4
            ],
            "t72_tank" => [
                "weight" => 5,
                "min_amount" => 0,
                "max_amount" => 1
            ],
            "desert_fox_bodyguard" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ]
        ]
    ],
    "arctic_drill" => [
        "name" => "Arctic Drill",
        "description" => "Every now and then, The United Nations host a military exercise in the northernmost regions of Tind. Even though the exercise is infamous for accidental deaths, we should join in to strengthen our relations with the Union.",
        "continent" => "tind",
        "spawn_weight" => 400,
        "rarity" => "Uncommon",
        "defender_division_name" => "UN Peacekeepers",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
        ],
        "enemies" => [
            "arctic_camo_expert" => [
                "weight" => 40,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "coast_guard" => [
                "weight" => 30,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "m2_bradley" => [
                "weight" => 30,
                "min_amount" => 3,
                "max_amount" => 5
            ]
        ]
    ],
    "thats_not_a_meteorite" => [
        "name" => "That's Not a Meteorite",
        "description" => "Well... This is interesting. As far as we know, the strange light that flashed across the sky near the eastern regions of Westberg was not a meteorite. Locals report heavy fighting in the area. We should investigate.",
        "continent" => "westberg",
        "spawn_weight" => 100,
        "rarity" => "Legendary",
        "defender_division_name" => "Alien Invaders",
        "rewards" => [
            ["resource" => "whz", "amount" => 25],
        ],
        "enemies" => [
            "sectopod" => [
                "weight" => 30,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "sectoid" => [
                "weight" => 70,
                "min_amount" => 7,
                "max_amount" => 10
            ]
        ]
    ],
];
