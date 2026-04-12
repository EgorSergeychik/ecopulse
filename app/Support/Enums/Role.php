<?php

namespace App\Support\Enums;

enum Role: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case OPERATOR = 'operator';
}
