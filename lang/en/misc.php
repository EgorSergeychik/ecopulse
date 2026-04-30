<?php

return [
    'messages' => [
        'profile_updated' => 'Profile updated.',
        'password_updated' => 'Password updated.',
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
];
