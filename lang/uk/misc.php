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
        'controller_unavailable' => 'Не вдалося отримати дані карти. Спробуйте пізніше.',
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
            'battery_low'          => 'Низький заряд батареї',
            'co2_warning'          => 'Підвищений рівень CO₂',
            'co2_critical'         => 'Небезпечний рівень CO₂',
            'noise_warning'        => 'Підвищений рівень шуму',
            'noise_critical'       => 'Критичний рівень шуму',
            'pm25_warning'         => 'Підвищений вміст дрібнодисперсного пилу (PM2.5)',
            'pm25_critical'        => 'Небезпечний вміст дрібнодисперсного пилу (PM2.5)',
            'temperature_warning'  => 'Висока температура',
            'temperature_critical' => 'Критична температура',
            'humidity_high'        => 'Висока вологість',
            'humidity_low'         => 'Низька вологість',
            'aqi_warning'          => 'Погана якість повітря',
            'aqi_critical'         => 'Дуже погана якість повітря',
            'radiation_warning'    => 'Підвищений радіаційний фон',
            'radiation_critical'   => 'Небезпечний радіаційний фон',
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
