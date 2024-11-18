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
            ]
        ]
        ],
        'barracks' => [
        'name' => 'Barracks',
        'description' => 'A military training facility. Allows you to train infantry units.',
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
            ]
        ]
    ]
];
