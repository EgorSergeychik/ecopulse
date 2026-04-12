<?php

namespace App\Support\Enums;

enum Permission: string
{
    // Dashboard
    case ViewDashboard = 'dashboard.view';

    // Users
    case ManageUsers = 'users.manage';
}
