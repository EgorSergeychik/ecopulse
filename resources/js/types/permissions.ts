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
}

export type PermissionKey = `${Permission}`;
