<?php

$BUILDING_CONFIG = [
    'geologist_building' => [
        'name' => 'Department of Geomatics and Mining Engineering',
        'description' => 'A research facility dedicated to the study of Earth\'s mineral resources. Allows you to gather mined resources.',
        'levels' => [
            1 => [
                'minimum_tier' => 1,
                'construction_cost' => [
                    'money' => 2000,
                    'building_materials' => 500,
                    'construction_time' => 60
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ],
            2 => [
                'minimum_tier' => 2,
                'construction_cost' => [
                    'money' => 5000,
                    'building_materials' => 1000,
                    'metal' => 250,
                    'construction_time' => 180
                ],
                'land' => [
                    'cleared_land' => 10
                ]
                ],
            3 => [
                'minimum_tier' => 3,
                'construction_cost' => [
                    'money' => 25000,
                    'building_materials' => 2500,
                    'metal' => 500,
                    'construction_time' => 480
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ]
        ]
    ],
    'zoologist_building' => [
        'name' => 'Institute of Zoological Sciences and Ecological Research',
        'description' => 'A specialized research center focused on animal biology and behavior. Allows you to gather livestock resources.',
        'levels' => [
            1 => [
                'minimum_tier' => 1,
                'construction_cost' => [
                    'money' => 2000,
                    'building_materials' => 500,
                    'construction_time' => 60
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ],
            2 => [
                'minimum_tier' => 2,
                'construction_cost' => [
                    'money' => 5000,
                    'building_materials' => 1000,
                    'metal' => 250,
                    'construction_time' => 180
                ],
                'land' => [
                    'cleared_land' => 10
                ]
                ],
            3 => [
                'minimum_tier' => 3,
                'construction_cost' => [
                    'money' => 25000,
                    'building_materials' => 2500,
                    'metal' => 500,
                    'construction_time' => 480
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ]
        ]
    ],
    'herbalist_building' => [
        'name' => 'Division of Flora Studies and Environmental Horticulture',
        'description' => 'A botanical research facility that studies plant life and cultivation techniques. Allows you to gather cultivated resources.',
        'levels' => [
            1 => [
                'minimum_tier' => 1,
                'construction_cost' => [
                    'money' => 2000,
                    'building_materials' => 500,
                    'construction_time' => 60
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ],
            2 => [
                'minimum_tier' => 2,
                'construction_cost' => [
                    'money' => 5000,
                    'building_materials' => 1000,
                    'metal' => 250,
                    'construction_time' => 180
                ],
                'land' => [
                    'cleared_land' => 10
                ]
                ],
            3 => [
                'minimum_tier' => 3,
                'construction_cost' => [
                    'money' => 25000,
                    'building_materials' => 2500,
                    'metal' => 500,
                    'construction_time' => 480
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ]
        ]
    ],
    'marine_biologist_building' => [
        'name' => 'Center for Oceanic Biology and Marine Resource Studies',
        'description' => 'An advanced facility for studying marine ecosystems and aquatic life. Allows you to gather fish resources.',
        'levels' => [
            1 => [
                'minimum_tier' => 1,
                'construction_cost' => [
                    'money' => 2000,
                    'building_materials' => 500,
                    'construction_time' => 60
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ],
            2 => [
                'minimum_tier' => 2,
                'construction_cost' => [
                    'money' => 5000,
                    'building_materials' => 1000,
                    'metal' => 250,
                    'construction_time' => 180
                ],
                'land' => [
                    'cleared_land' => 10
                ]
                ],
            3 => [
                'minimum_tier' => 3,
                'construction_cost' => [
                    'money' => 25000,
                    'building_materials' => 2500,
                    'metal' => 500,
                    'construction_time' => 480
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ]
        ]
    ],
    'barracks' => [
        'name' => 'Barracks',
        'description' => 'A military training facility. Allows you to train infantry units. You can recruit up to (3 times the level of this building) units at a time.',
        'levels' => [
            1 => [
                'minimum_tier' => 2,
                'construction_cost' => [
                    'money' => 5000,
                    'building_materials' => 1000,
                    'metal' => 250,
                    'construction_time' => 180
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ],
            2 => [
                'minimum_tier' => 3,
                'construction_cost' => [
                    'money' => 25000,
                    'building_materials' => 2500,
                    'metal' => 500,
                    'construction_time' => 480
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ]
        ]
    ],
    'special_forces_recruiting_agency' => [
        'name' => 'Special Forces Recruiting Agency',
        'description' => '[CLASSIFIED]',
        'levels' => [
            1 => [
                'minimum_tier' => 3,
                'construction_cost' => [
                    'money' => 25000,
                    'building_materials' => 2500,
                    'metal' => 500,
                    'construction_time' => 480
                ],
                'land' => [
                    'cleared_land' => 10
                ]
            ]
        ]
    ],
    'light_armour_factory' => [
            'name' => 'Light Armour Factory',
            'description' => 'A lightly-armoured vehicle factory. Allows you to train lightly armoured units. You can recruit up to (3 times the level of this building) units at a time.',
            'levels' => [
                1 => [
                    'minimum_tier' => 5,
                    'construction_cost' => [
                        'money' => 50000,
                        'building_materials' => 10000,
                        'metal' => 3000,
                        'construction_time' => 1440
                    ],
                    'land' => [
                        'cleared_land' => 20
                    ]
                ],
            ]
    ],
    'heavy_armour_factory' => [
    'name' => 'Heavy Armour Factory',
    'description' => 'A heavily-armoured vehicle factory. Allows you to train heavily armoured units. You can recruit up to (3 times the level of this building) units at a time.',
    'levels' => [
        1 => [
            'minimum_tier' => 5,
            'construction_cost' => [
                'money' => 50000,
                'building_materials' => 10000,
                'metal' => 3000,
                'construction_time' => 1440
            ],
            'land' => [
                'cleared_land' => 20
            ]
        ],
        ]
    ],
    'engineer_lab' => [
    'name' => 'Engineer Lab',
    'description' => 'A research centre for engineers to produce their creations. Allows you to train static units. You can recruit up to (3 times the level of this building) units at a time.',
    'levels' => [
        1 => [
            'minimum_tier' => 5,
            'construction_cost' => [
                'money' => 50000,
                'building_materials' => 10000,
                'metal' => 3000,
                'construction_time' => 1440
            ],
            'land' => [
                'cleared_land' => 20
            ]
        ],
        ]
    ],
    'airfield' => [
        'name' => 'Airfield',
        'description' => 'A base for aircraft. Allows you to train air units. You can recruit up to (3 times the level of this building) units at a time.',
        'levels' => [
            1 => [
                'minimum_tier' => 6,
                'construction_cost' => [
                    'money' => 50000,
                    'building_materials' => 10000,
                    'metal' => 3000,
                    'construction_time' => 1440
                ],
                'land' => [
                    'cleared_land' => 20
                ]
            ],
        ]
    ]
];
