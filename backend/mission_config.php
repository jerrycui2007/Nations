<?php

global $MISSION_CONFIG;

$MISSION_CONFIG = [
    "riot_control" => [
        "name" => "Riot Control",
        "description" => "A Westberg member of the United Nations is experiencing serious riot problems after a somewhat shady election. As a fellow UN member, it is our duty to help them.",
        "continent" => "westberg",
        "spawn_weight" => 240,
        "rarity" => "Common",
        "defender_division_name" => "Westberg Rioters",
        "rewards" => [
            ["resource" => "money", "amount" => 1000]
        ],
        "enemies" => [
            "rioter" => [
                "weight" => 100,
                "min_amount" => 9,
                "max_amount" => 11
            ]
        ]
    ],
    "its_the_least_we_can_do" => [
        "name" => "It's the Least We Can Do",
        "description" => "The United Nations is expecting us to contribute in the fight against the Bihadj Terrorists.",
        "continent" => "zaheria",
        "spawn_weight" => 240,
        "rarity" => "Common",
        "defender_division_name" => "Bihadj Insurgents",
        "rewards" => [
            ["resource" => "money", "amount" => 1000],
            ["resource" => "ammunition", "amount" => 20]
        ],
        "enemies" => [
            "ak47_infantry" => [
                "weight" => 70,
                "min_amount" => 5,
                "max_amount" => 7
            ],
            "rpg_infantry" => [
                "weight" => 25,
                "min_amount" => 1,
                "max_amount" => 3
            ],
            "technical" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 3
            ]
        ]
    ],
    "foreign_affairs" => [
        "name" => "Foreign Affairs",
        "description" => "Hey! Look over there, Mr. President! *Boom!* *Boom!* Take the cash and run, dammit!",
        "continent" => "westberg",
        "spawn_weight" => 240,
        "rarity" => "Common",
        "defender_division_name" => "Secret Service",
        "rewards" => [
            ["resource" => "money", "amount" => 10000]
        ],
        "enemies" => [
            "secret_agent" => [
                "weight" => 100,
                "min_amount" => 9,
                "max_amount" => 11
            ]
        ]
    ],  
    "suppressing_the_khev_minosk" => [
        "name" => "Suppressing the Khev Minosk",
        "description" => "We need to stop the advancements of the terror group Khev Minosk. Strike with force.",
        "continent" => "westberg",
        "spawn_weight" => 240,
        "rarity" => "Common",
        "defender_division_name" => "Khev Minosk",
        "rewards" => [
            ["resource" => "money", "amount" => 1000]
        ],
        "enemies" => [
            "ak47_infantry" => [
                "weight" => 70,
                "min_amount" => 6,
                "max_amount" => 8
            ],
            "rpg_infantry" => [
                "weight" => 30,
                "min_amount" => 2,
                "max_amount" => 4
            ],
        ]
    ],
    "homeland_offence" => [
        "name" => "Homeland Offence",
        "description" => "To be frank, we don't like Oldenburg. It's an irrelevant little nation on the continent of Westberg, and it's mere existence is annoying. We should attack them a bit.",
        "continent" => "westberg",
        "spawn_weight" => 240,
        "rarity" => "Common",
        "defender_division_name" => "Oldenburg Defence",
        "rewards" => [
            ["resource" => "money", "amount" => 500],
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
                "min_amount" => 8,
                "max_amount" => 10
            ],
        ]
    ],
    "supply_raid" => [
        "name" => "Supply Raid",
        "description" => "Strategically attacking supply depots of Bihadj outposts can prove valuable.",
        "continent" => "zaheria",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Bihadj Insurgents",
        "rewards" => [
            ["resource" => "metal", "amount" => 25],
            ["resource" => "building_materials", "amount" => 100],
            ["resource" => "fuel", "amount" => 10],
            ["resource" => "ammunition", "amount" => 25],
        ],
        "enemies" => [
            "ak47_infantry" => [
                "weight" => 40,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "rpg_infantry" => [
                "weight" => 20,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "t72_tank" => [
                "weight" => 15,
                "min_amount" => 1,
                "max_amount" => 3
            ],
            "desert_fox_bodyguard" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 3
            ],
            "concrete_bunker" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "zpu_4" => [
                "weight" => 15,
                "min_amount" => 1,
                "max_amount" => 3
            ],
        ]
    ],
    "anti_riot_control" => [
        "name" => "Anti-Riot Control",
        "description" => "A authoritarian police state is cracking down on protestors after a shady election. As a fellow UN member, it is our duty to help them.",
        "continent" => "tind",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Federal Security Service",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
        ],
        "enemies" => [
            "riot_cop" => [
                "weight" => 50,
                "min_amount" => 7,
                "max_amount" => 9
            ],
            "k9_team" => [
                "weight" => 25,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "signals_jammer" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "mh_6_little_bird" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 1
            ],
            "secret_agent" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 3
            ],
            "armoured_combat_ambulance" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 1
            ],
        ]
    ],
    "temperate_terrorists" => [
        "name" => "Temperate Terrorists",
        "description" => "The eastern nations of Westberg struggle with frequent raids by the terrorist group Khev Minosk. They are known for their reliable tanks, and we should be able to scrap them for metal if we succeed in our attack.",
        "continent" => "zaheria",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Khev Minosk Armoured Division",
        "rewards" => [
            ["resource" => "metal", "amount" => 250],
        ],
        "enemies" => [
            "bmp_1" => [
                "weight" => 20,
                "min_amount" => 3,
                "max_amount" => 4
            ],
            "t72_tank" => [
                "weight" => 20,
                "min_amount" => 3,
                "max_amount" => 4
            ],
            "stridsvagn_122" => [
                "weight" => 50,
                "min_amount" => 6,
                "max_amount" => 8
            ],
            "volvo_repair_truck" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ]
        ]
    ],
    "the_ace_of_spades" => [
        "name" => "The Ace of Spades",
        "description" => 'Francesco, the "Ace of Spades", is a legendary mobster with a deep and intense love for card games. We should attack his estate and rob the funds he uses to buy all of his cards.',
        "continent" => "san_sebastian",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "The Spades",
        "rewards" => [
            ["resource" => "money", "amount" => 15000],
        ],
        "enemies" => [
            "francesco_the_ace_of_spades" => [
                "weight" => 100,
                "min_amount" => 1,
                "max_amount" => 1
            ],
            "mh_6_little_bird" => [
                "weight" => 10,
                "min_amount" => 0,
                "max_amount" => 2
            ],
            "sniper" => [
                "weight" => 10,
                "min_amount" => 0,
                "max_amount" => 2
            ],
            "technical" => [
                "weight" => 10,
                "min_amount" => 0,
                "max_amount" => 2
            ],
            "mobster" => [
                "weight" => 70,
                "min_amount" => 10,
                "max_amount" => 12
            ],
        ]
    ],
    "overseas_investments" => [
        "name" => "Overseas Investments",
        "description" => 'A promising investment opportunity has open up in the nation of Dul Kaddir. The problem is that the local government is opposed to our so called "investment strategies". We should install a new government that is slightly more friendly to our "democratic" ideas.',
        "continent" => "zaheria",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Dul Kaddir Armed Forces",
        "rewards" => [
            ["resource" => "petroleum", "amount" => 50],
        ],
        "enemies" => [
            "fortified_bunker" => [
                "weight" => 100,
                "min_amount" => 1,
                "max_amount" => 1
            ],
            "concrete_bunker" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "zpu_4" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "mi_24_hind" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "m1a1_swift_desert_platoon" => [
                "weight" => 20,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "trench_infantry" => [
                "weight" => 50,
                "min_amount" => 7,
                "max_amount" => 9
            ],
        ]
    ],
    "the_desert_fox" => [
        "name" => "The Desert Fox",
        "description" => 'The Bihadj Leader, known only as "The Desert Fox", is currently residing in the western part of Zaheria. With a precise strike, we should be able to take him out and cripple his organization.',
        "continent" => "zaheria",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Bihdaj Bodyguards",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
            ["resource" => "ammunition", "amount" => 50]
        ],
        "enemies" => [
            "desert_fox_bodyguard" => [
                "weight" => 50,
                "min_amount" => 7,
                "max_amount" => 8
            ],
            "ak47_infantry" => [
                "weight" => 20,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "technical" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "bmp_1" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "mh_6_little_bird" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "medic" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ]
        ]
    ],
    "border_patrol" => [
        "name" => "Border Patrol",
        "description" => 'An uptick in criminal activities across multiple contiennts by various organized crime groups has left citizens calling for a military response. Members of the Black Horns, Khev Minosk, and Bihadj Insurgents are thought to be responsible of drug trafficking and money laundering. Strike with force.',
        "continent" => "amarino",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Drug Smugglers",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
            ["resource" => "herbs", "amount" => 1],
            ["resource" => "hemp", "amount" => 1],
        ],
        "enemies" => [
            "black_horn" => [
                "weight" => 25,
                "min_amount" => 3,
                "max_amount" => 4
            ],
            "ak47_infantry" => [
                "weight" => 25,
                "min_amount" => 3,
                "max_amount" => 4
            ],
            "technical" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "mobster" => [
                "weight" => 25,
                "min_amount" => 3,
                "max_amount" => 4
            ],
            "mh_6_little_bird" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "sniper" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "signals_jammer" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ]
        ]
    ],
    "foreign_affairs_ii" => [
        "name" => "Foreign Affairs II",
        "description" => 'Hey! Look over there, Mr. President! *Boom!* *Boom!* Take the cash and run, dammit!',
        "continent" => "amarino",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Federal Security Service",
        "rewards" => [
            ["resource" => "money", "amount" => 25000]
        ],
        "enemies" => [
            "armoured_limousine" => [
                "weight" => 100,
                "min_amount" => 1,
                "max_amount" => 1
            ],
            "armoured_combat_ambulance" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "riot_cop" => [
                "weight" => 10,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "k9_team" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "mh_6_little_bird" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "sniper" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "signals_jammer" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "secret_agent" => [
                "weight" => 65,
                "min_amount" => 4,
                "max_amount" => 6
            ]
        ]
    ],
    "heat" => [
        "name" => "Heat",
        "description" => 'The Khev Minosk have captured a depot of anti-tank weapons, and are now digging in a defence. As a United Nations member, we must relieve them of their weaponry quickly.',
        "continent" => "westberg",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Khev Minosk Anti-Armour Company",
        "rewards" => [
            ["resource" => "money", "amount" => 2500]
        ],
        "enemies" => [
            "rpg_infantry" => [
                "weight" => 20,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "at4_infantry" => [
                "weight" => 20,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "javelin_infantry" => [
                "weight" => 40,
                "min_amount" => 5,
                "max_amount" => 7
            ],
            "tank_destroyer" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "mq_9_reaper" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
        ]
    ],
    "little_brother_wants_out" => [
        "name" => "Little Brother Wants Out",
        "description" => "Diplomatic negotiations over a small dependent region in San Sebastian had recently escalated into a armed conflict between state and regional rebels. As a fellow member of the United Nations, we are obliged to help.",
        "continent" => "san_sebastian",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Tyrian Rebels",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
            ["resource" => "building_materials", "amount" => 100]
        ],
        "enemies" => [
            "medic" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "infantry" => [
                "weight" => 30,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "horseback_infantry" => [
                "weight" => 30,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "merkava_iv" => [
                "weight" => 30,
                "min_amount" => 4,
                "max_amount" => 6
            ],
        ]
    ],
    
    "jungle_fever" => [
        "name" => "Jungle Fever",
        "description" => "The feared Followers of Black Horn is one of the reasons why southern Amarino is in such an unstable state. They are deadly when confronted in the jungle, but they have to be stopped.",
        "continent" => "amarino",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "Followers of Black Horn",
        "rewards" => [
            ["resource" => "money", "amount" => 8000]
        ],
        "enemies" => [
            "black_horn" => [
                "weight" => 85,
                "min_amount" => 10,
                "max_amount" => 15
            ],
            "centurion_mk_5" => [
                "weight" => 15,
                "min_amount" => 1,
                "max_amount" => 3
            ]
        ]
    ],
    "arctic_drill" => [
        "name" => "Arctic Drill",
        "description" => "Every now and then, The United Nations host a military exercise in the northernmost regions of Tind. Even though the exercise is infamous for accidental deaths, we should join in to strengthen our relations with the Union.",
        "continent" => "tind",
        "spawn_weight" => 45,
        "rarity" => "Uncommon",
        "defender_division_name" => "UN Peacekeepers",
        "rewards" => [
            ["resource" => "money", "amount" => 2000],
        ],
        "enemies" => [
            "arctic_camo_expert" => [
                "weight" => 30,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "coast_guard" => [
                "weight" => 20,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "m2_bradley" => [
                "weight" => 20,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "leopard_2" => [
                "weight" => 30,
                "min_amount" => 4,
                "max_amount" => 6
            ]
        ]
    ],
    "dul_kaddir_convoy_interception" => [
        "name" => "Dul Kaddir Convoy Interception",
        "description" => "A heavily armed Dul Kaddir convoy is expected to pass through the T'el valley, opening up an opportunity for us to strike and intercept.",
        "continent" => "zaheria",
        "spawn_weight" => 25,
        "rarity" => "Rare",
        "defender_division_name" => "Dul Kaddir Armoured Convoy",
        "rewards" => [
            ["resource" => "money", "amount" => 5000],
            ["resource" => "metal", "amount" => 50],
            ["resource" => "ammunition", "amount" => 100],
            ["resource" => "fuel", "amount" => 25],
        ],
        "enemies" => [
            "volvo_repair_truck" => [
                "weight" => 100,
                "min_amount" => 2,
                "max_amount" => 2
            ],
            "bmp_1" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "humvee" => [
                "weight" => 10,
                "min_amount" => 2,
                "max_amount" => 3
            ],
            "m1a1_abrahms" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "m1a1_swift_desert_platoon" => [
                "weight" => 30,
                "min_amount" => 7,
                "max_amount" => 9
            ],
            "tank_destroyer" => [
                "weight" => 15,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "k2_black_panther" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "m2_bradley" => [
                "weight" => 15,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "2k22_tunguska" => [
                "weight" => 15,
                "min_amount" => 3,
                "max_amount" => 5
            ],
        ]
    ],
    "all_ghillied_up" => [
        "name" => "All Ghillied Up",
        "description" => "The Khev Minosk has been reportedly stealing uranium from an abandoned nuclear power plant. However, it seems that they have no soldiers with them . . .?",
        "continent" => "westberg",
        "spawn_weight" => 25,
        "rarity" => "Rare",
        "defender_division_name" => "Khev Minosk Special Forces",
        "rewards" => [
            ["resource" => "money", "amount" => 5000],
            ["resource" => "uranium", "amount" => 50]
        ],
        "enemies" => [
            "forest_camo_expert" => [
                "weight" => 20,
                "min_amount" => 6,
                "max_amount" => 8
            ],
            "sniper" => [
                "weight" => 20,
                "min_amount" => 6,
                "max_amount" => 8
            ],
            "gear_sniper" => [
                "weight" => 50,
                "min_amount" => 6,
                "max_amount" => 8
            ],
            "signals_jammer" => [
                "weight" => 5,
                "min_amount" => 0,
                "max_amount" => 2
            ],
            "mh_6_little_bird" => [
                "weight" => 5,
                "min_amount" => 0,
                "max_amount" => 2
            ],
        ]
    ],
    "the_mortar_rain_of_alba_nera" => [
        "name" => "The Mortar Rain of Alba Nera",
        "description" => "Exploding hail falling from the skies. Alba Nera neighbours tremble. Literally. We have to stop this.",
        "continent" => "san_sebastian",
        "spawn_weight" => 25,
        "rarity" => "Rare",
        "defender_division_name" => "Alba Nera Artillery Brigade",
        "rewards" => [
            ["resource" => "money", "amount" => 10000],
            ["resource" => "ammunition", "amount" => 500],
        ],
        "enemies" => [
            "medic" => [
                "weight" => 100,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "mortar_infantry" => [
                "weight" => 50,
                "min_amount" => 9,
                "max_amount" => 11
            ],
            "zpu_4" => [
                "weight" => 10,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "mq_9_reaper" => [
                "weight" => 10,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "javelin_infantry" => [
                "weight" => 30,
                "min_amount" => 4,
                "max_amount" => 6
            ],
        ]
    ],
    "dust_to_dust" => [
        "name" => "Dust to Dust",
        "description" => "A hotel in Zaheria is harbouring dangerous terrorists. Although the security is heavily armed, this is our best opportunity to take them down.",
        "continent" => "zaheria",
        "spawn_weight" => 25,
        "rarity" => "Rare",
        "defender_division_name" => "Oasis Hotel Security Service",
        "rewards" => [
            ["resource" => "money", "amount" => 15000],
            ["resource" => "ammunition", "amount" => 50],
        ],
        "enemies" => [
            "gear_infantry" => [
                "weight" => 50,
                "min_amount" => 9,
                "max_amount" => 11
            ],
            "medic" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "rpg_infantry" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "mg_infantry" => [
                "weight" => 20,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "mh_6_little_bird" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "riot_cop" => [
                "weight" => 10,
                "min_amount" => 3,
                "max_amount" => 5
            ],
        ]
    ],
    "the_impenetrable_norrland_wall" => [
        "name" => "The Impenetrable Norrland Wall",
        "description" => "The Norrland conflict has recently escalated and taking the fight to them may be the only way to make them yield.",
        "continent" => "tind",
        "spawn_weight" => 25,
        "rarity" => "Rare",
        "defender_division_name" => "The Norrland Wall",
        "rewards" => [
            ["resource" => "money", "amount" => 7500],
            ["resource" => "building_materials", "amount" => 500],
            ["resource" => "metal", "amount" => 500],
        ],
        "enemies" => [
            "national_guard" => [
                "weight" => 20,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "arctic_camo_expert" => [
                "weight" => 20,
                "min_amount" => 3,
                "max_amount" => 5
            ],
            "concrete_bunker" => [
                "weight" => 30,
                "min_amount" => 6,
                "max_amount" => 8
            ],
            "combat_engineer" => [
                "weight" => 20,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "2k22_tunguska" => [
                "weight" => 25,
                "min_amount" => 3,
                "max_amount" => 5
            ]
        ]
    ],
    "rumble_in_the_never_mind" => [
        "name" => "Rumble in the . . . Never Mind",
        "description" => "Have you ever been to the jungle? I have heard it's supposed to be warm. And sometimes wet. And full of unpleasant wildlife. We could send a 'delegation' to steal some of their fauna. They could be useful enough to make it worth the risk.",
        "continent" => "amarino",
        "spawn_weight" => 25,
        "rarity" => "Rare",
        "defender_division_name" => "World Wildlife Fund",
        "rewards" => [
            ["resource" => "money", "amount" => 10000],
            ["resource" => "panther", "amount" => 100],
            ["resource" => "elephant", "amount" => 100],
            ["resource" => "piranha", "amount" => 100],
        ],
        "enemies" => [
            "a10_thunderbolt" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "navy_seal" => [
                "weight" => 70,
                "min_amount" => 14,
                "max_amount" => 16
            ],
            "humvee" => [
                "weight" => 15,
                "min_amount" => 2,
                "max_amount" => 3
            ],
            "signals_jammer" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 3
            ]
        ]
    ],
    "venland_airspace_violations" => [
        "name" => "Venland Airspace Violations",
        "description" => "Oldenburg is constantly harassing Venland with unprecedented airspace violations. As a United Nations member we have been called upon to teach Oldenburg a lesson.",
        "continent" => "westberg",
        "spawn_weight" => 10,
        "rarity" => "Epic",
        "defender_division_name" => "2nd Oldenburg Air Squadron",
        "rewards" => [
            ["resource" => "money", "amount" => 10000],
            ["resource" => "fuel", "amount" => 1000],
            ["resource" => "ammunition", "amount" => 5000]
        ],
        "enemies" => [
            "f_35_lightning_ii" => [
                "weight" => 20,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "a10_thunderbolt" => [
                "weight" => 20,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "f_22_raptor" => [
                "weight" => 20,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "mi_24_hind" => [
                "weight" => 20,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "mq_9_reaper" => [
                "weight" => 20,
                "min_amount" => 4,
                "max_amount" => 6
            ]
        ]
    ],
    "convoy_interception_interception" => [
        "name" => "Convoy Interception Interception",
        "description" => "Our attempt to intercept an armoured convoy in the T'el valley led us straight into an ambush! Quick, we must counterattack!",
        "continent" => "zaheria",
        "spawn_weight" => 10,
        "rarity" => "Epic",
        "defender_division_name" => "Dul Kaddir Air Force",
        "rewards" => [
            ["resource" => "money", "amount" => 10000],
            ["resource" => "fuel", "amount" => 1000],
            ["resource" => "ammunition", "amount" => 5000]
        ],
        "enemies" => [
            "mq_9_reaper" => [
                "weight" => 25,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "mq_20_avenger" => [
                "weight" => 25,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "pave_low" => [
                "weight" => 25,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "b_2_spirit" => [
                "weight" => 25,
                "min_amount" => 4,
                "max_amount" => 6
            ]
        ]
    ],
    "thats_not_a_comet" => [
        "name" => "That's not a Comet",
        "description" => "Our intelligence suggests that Alba Nera has developed nuclear powered rifles. We must take them all out before the technology spreads.",
        "continent" => "san_sebastian",
        "spawn_weight" => 10,
        "rarity" => "Epic",
        "defender_division_name" => "Alba Nera Rocket Snipers",
        "rewards" => [
            ["resource" => "money", "amount" => 10000],
            ["resource" => "uranium", "amount" => 500],
            ["resource" => "ammunition", "amount" => 5000]
        ],
        "enemies" => [
            "medic" => [
                "weight" => 100,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "rocket_sniper" => [
                "weight" => 50,
                "min_amount" => 9,
                "max_amount" => 11
            ],
            "nuclear_rocket_sniper" => [
                "weight" => 50,
                "min_amount" => 9,
                "max_amount" => 11
            ]
        ]
    ],
    "the_second_norrland_wall" => [
        "name" => "The Second Norrland Wall",
        "description" => "As we approach the heavily defended Norrland capital, a new, devstating weapon has been revealed . . .",
        "continent" => "tind",
        "spawn_weight" => 10,
        "rarity" => "Epic",
        "defender_division_name" => "Norrland Bunker Complex",
        "rewards" => [
            ["resource" => "money", "amount" => 10000],
            ["resource" => "building_materials", "amount" => 1000],
            ["resource" => "metal", "amount" => 500],
            ["resource" => "ammunition", "amount" => 1000],
            ["resource" => "fuel", "amount" => 500]
        ],
        "enemies" => [
            "railgun" => [
                "weight" => 100,
                "min_amount" => 1,
                "max_amount" => 1
            ],
            "fortified_bunker" => [
                "weight" => 50,
                "min_amount" => 9,
                "max_amount" => 11
            ],
            "zpu_4" => [
                "weight" => 10,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "2k22_tunguska" => [
                "weight" => 10,
                "min_amount" => 2,
                "max_amount" => 4
            ],
            "signals_jammer" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "tyz_uav_engineer" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "type_99a" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "leopard_2" => [
                "weight" => 5,
                "min_amount" => 1,
                "max_amount" => 2
            ]
        ]
    ],
    "thats_not_a_meteorite" => [
        "name" => "That's not a Meteorite",
        "description" => "Well... This is interesting. As far as we know, the strange light that flashed across the sky near the eastern regions of Westberg was not a meteorite. Locals report heavy fighting in the area. We should investigate.",
        "continent" => "westberg",
        "spawn_weight" => 2,
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
    "the_hounds_of_spring" => [
        "name" => "The Hounds of Spring",
        "description" => "What’s that? An anomaly has been detected at the edge of our nation’s borders. The once thought destroyed Hounds of Alba are alive! We must send a pre-emptive strike to ensure they do not grow strong enough to exact revenge on us.",
        "continent" => "san_sebastian",
        "spawn_weight" => 3,
        "rarity" => "Legendary",
        "defender_division_name" => "The Black Hounds of Alba Nera",
        "rewards" => [
            ["resource" => "money", "amount" => 100000],
            ["resource" => "uranium", "amount" => 1000],
            ["resource" => "whz", "amount" => 10],
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
    "death_from_above" => [
        "name" => "Death From Above",
        "description" => "An unknown supporter of Alba Nera has provided them with an AC-130, giving them unprecendented air superiority. We must shoot it down before it's too late.",
        "continent" => "san_sebastian",
        "spawn_weight" => 3,
        "rarity" => "Legendary",
        "defender_division_name" => "Alba Nera Strike Force",
        "rewards" => [
            ["resource" => "money", "amount" => 10000],
            ["resource" => "fuel", "amount" => 1000],
            ["resource" => "ammunition", "amount" => 5000],
        ],
        "enemies" => [
            "lockheed_ac_130" => [
                "weight" => 100,
                "min_amount" => 1,
                "max_amount" => 1
            ],
            "forest_camo_expert" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "mi_24_hind" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "sukhoi_su_75_checkmate" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "b_2_spirit" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "javelin_infantry" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "signals_jammer" => [
                "weight" => 10,
                "min_amount" => 1,
                "max_amount" => 2
            ],
            "sniper" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "merkava_iv" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "f_22_raptor" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
            "2k22_tunguska" => [
                "weight" => 10,
                "min_amount" => 4,
                "max_amount" => 6
            ],
        ]
    ],
];
