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

    // Roles
    case ViewRoles = 'roles.view';
}
