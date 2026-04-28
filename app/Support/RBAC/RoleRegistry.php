<?php

namespace App\Support\RBAC;

use App\Support\Enums\Permission;

class RoleRegistry
{
    /** @var array<string, RoleDefinition> */
    private static array $definitions = [];

    public static function define(string $role, Permission ...$permissions): void
    {
        self::$definitions[$role] = new RoleDefinition($role, $permissions);
    }

    public static function for(string $role): RoleDefinition
    {
        return self::$definitions[$role] ?? new RoleDefinition($role, []);
    }
}
