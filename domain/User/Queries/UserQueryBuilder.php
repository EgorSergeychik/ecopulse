<?php

namespace Domain\User\Queries;

use App\Support\Enums\Role;
use Illuminate\Database\Eloquent\Builder;

class UserQueryBuilder extends Builder
{
    /*
     * Filters
     */
    public function search(string $search): self
    {
        return $this->where(function (Builder $query) use ($search) {
            $query
                ->where('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('users.role', 'like', "%{$search}%");
        });
    }

    /*
     * Roles
     */

    public function superadmin(): self
    {
        return $this->where('users.role', Role::SUPERADMIN->value);
    }

    public function operator(): self
    {
        return $this->where('users.role', Role::OPERATOR->value);
    }
}
