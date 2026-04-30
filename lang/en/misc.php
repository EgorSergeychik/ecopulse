<?php

return [
    'roles' => [
        \App\Support\Enums\Role::SUPERADMIN->value => 'Superadmin',
        \App\Support\Enums\Role::OPERATOR->value => 'Operator',
    ],
    'robots' => [
        'status' => [
            \Domain\Robot\Enums\RobotState::OFFLINE->value => 'Offline',
            \Domain\Robot\Enums\RobotState::ACTIVE->value => 'Active',
            \Domain\Robot\Enums\RobotState::ERROR->value => 'Error',
            \Domain\Robot\Enums\RobotState::MAINTENANCE->value => 'Maintenance',
        ],
    ],
];
