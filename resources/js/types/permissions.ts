export enum Permission {
    // Dashboard
    ViewDashboard = 'dashboard.view',

    // Users
    ViewUsers = 'users.view',
    CreateUsers = 'users.create',
    UpdateUsers = 'users.update',
    DeleteUsers = 'users.delete',

    // Zones
    ViewZones = 'zones.view',
    CreateZones = 'zones.create',
    UpdateZones = 'zones.update',
    DeleteZones = 'zones.delete',

    // Robots
    ViewRobots = 'robots.view',
    CreateRobots = 'robots.create',
    UpdateRobots = 'robots.update',
    TransitionRobots = 'robots.transition',
    DeleteRobots = 'robots.delete',

    // Telemetry
    ViewTelemetryLogs = 'telemetry-logs.view',

    // Incidents
    ViewIncidents = 'incidents.view',
    ResolveIncidents = 'incidents.resolve',
}

export type PermissionKey = `${Permission}`;
