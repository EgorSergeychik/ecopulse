<?php

return [
    'metrics' => [
        'battery_pct' => [
            'required'   => true,
            'validation' => ['numeric', 'between:0,100'],
            'fake'       => ['type' => 'float', 'min' => 5,    'max' => 96,  'decimals' => 2],
        ],
        'co2' => [
            'required'   => false,
            'validation' => ['numeric', 'min:0'],
            'fake'       => ['type' => 'int',   'min' => 380,  'max' => 2200],
        ],
        'noise_level' => [
            'required'   => false,
            'validation' => ['numeric', 'min:0'],
            'fake'       => ['type' => 'int',   'min' => 35,   'max' => 115],
        ],
        'pm25' => [
            'required'   => false,
            'validation' => ['numeric', 'min:0'],
            'fake'       => ['type' => 'float', 'min' => 2,    'max' => 90,  'decimals' => 1],
        ],
        'temperature' => [
            'required'   => false,
            'validation' => ['numeric', 'between:-50,60'],
            'fake'       => ['type' => 'float', 'min' => 14,   'max' => 42,  'decimals' => 1],
        ],
        'humidity' => [
            'required'   => false,
            'validation' => ['numeric', 'between:0,100'],
            'fake'       => ['type' => 'float', 'min' => 12,   'max' => 95,  'decimals' => 1],
        ],
        'aqi' => [
            'required'   => false,
            'validation' => ['numeric', 'min:0'],
            'fake'       => ['type' => 'int',   'min' => 10,   'max' => 220],
        ],
        'radiation' => [
            'required'   => false,
            'validation' => ['numeric', 'min:0'],
            'fake'       => ['type' => 'float', 'min' => 0.05, 'max' => 1.5, 'decimals' => 3],
        ],
    ],
];
