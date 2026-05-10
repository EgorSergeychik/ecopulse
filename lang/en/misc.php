<?php

return [
    'messages' => [
        'profile_updated' => 'Profile updated.',
        'password_updated' => 'Password updated.',
        'robot_token_regenerated' => 'Robot token regenerated.',
    ],
    'roles' => [
        \App\Support\Enums\Role::SUPERADMIN->value => 'Superadmin',
        \App\Support\Enums\Role::OPERATOR->value => 'Operator',
    ],
    'robots' => [
        'controller_unavailable' => 'Could not fetch map data. Please try again later.',
        'errors' => [
            'invalid_status' => 'The requested robot status is invalid.',
            'transition_not_allowed' => 'The requested robot status transition is not allowed.',
            'only_maintenance_and_offline_allowed' => 'Only maintenance and offline transitions are allowed.',
        ],
        'status' => [
            \Domain\Robot\Enums\RobotState::OFFLINE->value => 'Offline',
            \Domain\Robot\Enums\RobotState::ACTIVE->value => 'Active',
            \Domain\Robot\Enums\RobotState::ERROR->value => 'Error',
            \Domain\Robot\Enums\RobotState::MAINTENANCE->value => 'Maintenance',
        ],
    ],
    'incidents' => [
        'types' => [
            'battery_low'          => 'Low battery',
            'co2_warning'          => 'Elevated CO₂',
            'co2_critical'         => 'Dangerous CO₂ level',
            'noise_warning'        => 'High noise level',
            'noise_critical'       => 'Extreme noise level',
            'pm25_warning'         => 'Elevated fine dust (PM2.5)',
            'pm25_critical'        => 'Dangerous fine dust (PM2.5)',
            'temperature_warning'  => 'High temperature',
            'temperature_critical' => 'Extreme temperature',
            'humidity_high'        => 'High humidity',
            'humidity_low'         => 'Low humidity',
            'aqi_warning'          => 'Poor air quality',
            'aqi_critical'         => 'Very poor air quality',
            'radiation_warning'    => 'Elevated radiation',
            'radiation_critical'   => 'Dangerous radiation level',
        ],
        'severity' => [
            'warning' => 'Warning',
            'critical' => 'Critical',
            'fatal' => 'Fatal',
        ],
        'directions' => [
            'above' => 'above',
            'below' => 'below',
        ],
        'messages' => [
            'threshold_breach' => 'is :direction the threshold :threshold (actual: :actual).',
        ],
    ],
];
