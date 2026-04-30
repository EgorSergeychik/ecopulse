<?php

namespace Domain\Robot\Enums;

enum RobotState: string
{
    case OFFLINE = 'offline';
    case ACTIVE = 'active';
    case ERROR = 'error';
    case MAINTENANCE = 'maintenance';
}
