<?php

namespace Domain\Incident\Enums;

enum IncidentOperator: string
{
    case HIGHER = 'higher';
    case LOWER = 'lower';
}
