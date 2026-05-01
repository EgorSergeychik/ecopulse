<?php

namespace Domain\Incident\Enums;

enum IncidentSeverity: string
{
    case WARNING = 'warning';
    case CRITICAL = 'critical';
    case FATAL = 'fatal';
}
