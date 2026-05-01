<?php

return [
    'messages' => [
        'profile_updated' => 'Профіль оновлено.',
        'password_updated' => 'Пароль оновлено.',
        'robot_token_regenerated' => 'Токен робота перевипущено.',
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
    'incidents' => [
        'types' => [
            'battery_low' => 'Низький заряд батареї',
            'co2_high' => 'Високий рівень CO2',
            'noise_level_high' => 'Високий рівень шуму',
        ],
        'severity' => [
            'warning' => 'Попередження',
            'critical' => 'Критичний',
            'fatal' => 'Фатальний',
        ],
        'directions' => [
            'above' => 'вище',
            'below' => 'нижче',
        ],
        'messages' => [
            'threshold_breach' => ':direction порогу :threshold (фактично: :actual).',
        ],
    ],
];
