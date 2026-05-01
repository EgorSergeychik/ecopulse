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
            'battery_low' => 'Low battery',
            'co2_high' => 'High CO2',
            'noise_level_high' => 'High noise level',
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
