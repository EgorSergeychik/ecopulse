<?php

return [
    'messages' => [
        'profile_updated' => 'Профіль оновлено.',
        'password_updated' => 'Пароль оновлено.',
    ],
    'roles' => [
        \App\Support\Enums\Role::SUPERADMIN->value => 'Суперадмін',
        \App\Support\Enums\Role::OPERATOR->value => 'Оператор',
    ],
    'robots' => [
        'errors' => [
            'invalid_status' => 'Запитаний статус робота некоректний.',
            'transition_not_allowed' => 'Запитаний перехід статусу робота недозволений.',
            'only_maintenance_and_offline_allowed' => 'Дозволені лише переходи в технічне обслуговування та офлайн.',
        ],
        'status' => [
            \Domain\Robot\Enums\RobotState::OFFLINE->value => 'Офлайн',
            \Domain\Robot\Enums\RobotState::ACTIVE->value => 'Активний',
            \Domain\Robot\Enums\RobotState::ERROR->value => 'Помилка',
            \Domain\Robot\Enums\RobotState::MAINTENANCE->value => 'Технічне обслуговування',
        ],
    ],
];
