<?php

use Domain\Incident\Enums\IncidentOperator;
use Domain\Incident\Enums\IncidentSeverity;

return [
    'rules' => [
        'battery_low' => [
            'field' => 'metrics.battery_pct',
            'operator' => IncidentOperator::LOWER->value,
            'value' => 10,
            'severity' => IncidentSeverity::WARNING->value,
        ],
        'co2_high' => [
            'field' => 'metrics.co2',
            'operator' => IncidentOperator::HIGHER->value,
            'value' => 1000,
            'severity' => IncidentSeverity::CRITICAL->value,
        ],
        'noise_level_high' => [
            'field' => 'metrics.noise_level',
            'operator' => IncidentOperator::HIGHER->value,
            'value' => 85,
            'severity' => IncidentSeverity::WARNING->value,
        ],
    ],
];
