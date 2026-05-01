<?php

namespace App\Support\Enums;

enum Permission: string
{
    // Dashboard
    case ViewDashboard = 'dashboard.view';

    // Users
    case ViewUsers = 'users.view';
    case CreateUsers = 'users.create';
    case UpdateUsers = 'users.update';
    case DeleteUsers = 'users.delete';

    // Zones
    case ViewZones = 'zones.view';
    case CreateZones = 'zones.create';
    case UpdateZones = 'zones.update';
    case DeleteZones = 'zones.delete';

    // Robots
    case ViewRobots = 'robots.view';
    case CreateRobots = 'robots.create';
    case UpdateRobots = 'robots.update';
    case TransitionRobots = 'robots.transition';
    case DeleteRobots = 'robots.delete';

    // Telemetry
    case ViewTelemetryLogs = 'telemetry-logs.view';

    // Incidents
    case ViewIncidents = 'incidents.view';
    case ResolveIncidents = 'incidents.resolve';
}
