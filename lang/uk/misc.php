<?php

return [
    'roles' => [
        \App\Support\Enums\Role::SUPERADMIN->value => 'Суперадмін',
        \App\Support\Enums\Role::OPERATOR->value => 'Оператор',
    ],
    'robots' => [
        'status' => [
            \Domain\Robot\Enums\RobotState::OFFLINE->value => 'Офлайн',
            \Domain\Robot\Enums\RobotState::ACTIVE->value => 'Активний',
            \Domain\Robot\Enums\RobotState::ERROR->value => 'Помилка',
            \Domain\Robot\Enums\RobotState::MAINTENANCE->value => 'Технічне обслуговування',
        ],
    ],
];
