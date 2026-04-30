<?php

namespace Domain\Robot\Queries;

use Domain\User\Queries\UserQueryBuilder;
use Illuminate\Database\Eloquent\Builder;

class RobotQueryBuilder extends Builder
{
    /*
     * Access
     */

    public function currentUser(): self
    {
        return $this->userId(auth()->id());
    }

    public function checkAccess(): self
    {
        if (auth()->user()->isSuperAdmin()) {
            return $this;
        }

        return $this->currentUser();
    }

    /*
     * Relations
     */

    public function userId(int $userId): self
    {
        return $this->whereHas('zone.users', fn (UserQueryBuilder $query) => $query
            ->where('users.id', $userId)
        );
    }

    /*
     * Filters
     */

    public function search(string $search): self
    {
        return $this->where(fn (self $query) => $query
            ->where('robots.name', 'like', "%{$search}%")
            ->orWhereRelation('zone', 'name', 'like', "%{$search}%")
            ->orWhereRelation('zone.users', 'name', 'like', "%{$search}%")
        );
    }
}
