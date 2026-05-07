<?php

use Domain\Incident\Enums\IncidentOperator;
use Domain\Incident\Enums\IncidentSeverity;

return [
    'rules' => [
        // Battery
        'battery_low' => [
            'field'    => 'metrics.battery_pct',
            'operator' => IncidentOperator::LOWER->value,
            'value'    => 10,
            'severity' => IncidentSeverity::WARNING->value,
        ],

        // CO2 (ppm)
        'co2_warning' => [
            'field'    => 'metrics.co2',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 1000,
            'severity' => IncidentSeverity::WARNING->value,
        ],
        'co2_critical' => [
            'field'    => 'metrics.co2',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 2000,
            'severity' => IncidentSeverity::CRITICAL->value,
        ],

        // Noise level (dB)
        'noise_warning' => [
            'field'    => 'metrics.noise_level',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 85,
            'severity' => IncidentSeverity::WARNING->value,
        ],
        'noise_critical' => [
            'field'    => 'metrics.noise_level',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 110,
            'severity' => IncidentSeverity::CRITICAL->value,
        ],

        // Fine dust PM2.5 (µg/m³)
        'pm25_warning' => [
            'field'    => 'metrics.pm25',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 35,
            'severity' => IncidentSeverity::WARNING->value,
        ],
        'pm25_critical' => [
            'field'    => 'metrics.pm25',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 75,
            'severity' => IncidentSeverity::CRITICAL->value,
        ],

        // Temperature (°C)
        'temperature_warning' => [
            'field'    => 'metrics.temperature',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 35,
            'severity' => IncidentSeverity::WARNING->value,
        ],
        'temperature_critical' => [
            'field'    => 'metrics.temperature',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 40,
            'severity' => IncidentSeverity::CRITICAL->value,
        ],

        // Humidity (%)
        'humidity_high' => [
            'field'    => 'metrics.humidity',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 80,
            'severity' => IncidentSeverity::WARNING->value,
        ],
        'humidity_low' => [
            'field'    => 'metrics.humidity',
            'operator' => IncidentOperator::LOWER->value,
            'value'    => 20,
            'severity' => IncidentSeverity::WARNING->value,
        ],

        // Air quality index (AQI)
        'aqi_warning' => [
            'field'    => 'metrics.aqi',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 100,
            'severity' => IncidentSeverity::WARNING->value,
        ],
        'aqi_critical' => [
            'field'    => 'metrics.aqi',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 200,
            'severity' => IncidentSeverity::CRITICAL->value,
        ],

        // Radiation background (µSv/h)
        'radiation_warning' => [
            'field'    => 'metrics.radiation',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 0.3,
            'severity' => IncidentSeverity::WARNING->value,
        ],
        'radiation_critical' => [
            'field'    => 'metrics.radiation',
            'operator' => IncidentOperator::HIGHER->value,
            'value'    => 1.2,
            'severity' => IncidentSeverity::CRITICAL->value,
        ],
    ],
];
