export enum Permission {
    // Dashboard
    ViewDashboard = 'dashboard.view',

    // Users
    ManageUsers = 'users.manage',
}

export type PermissionKey = `${Permission}`;
