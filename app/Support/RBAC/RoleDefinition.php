<?php

namespace App\Support\RBAC;

use App\Support\Enums\Permission;

final class RoleDefinition
{
    /** @param Permission[] $permissions */
    public function __construct(
        public readonly string $role,
        private readonly array $permissions,
    ) {}

    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}
