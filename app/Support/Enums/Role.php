<?php

namespace App\Support\Enums;

enum Role: string
{
    case SUPERADMIN = 'superadmin';
    case OPERATOR = 'operator';
}
