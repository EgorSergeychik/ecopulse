export enum Permission {
    // Dashboard
    ViewDashboard = 'dashboard.view',

    // Users
   ViewUsers = 'users.view',
   CreateUsers = 'users.create',
   UpdateUsers = 'users.update',
   DeleteUsers = 'users.delete',
}

export type PermissionKey = `${Permission}`;
