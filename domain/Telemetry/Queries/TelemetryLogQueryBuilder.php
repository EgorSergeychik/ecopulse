<?php

namespace Domain\Telemetry\Queries;

use Domain\Robot\Queries\RobotQueryBuilder;
use Domain\User\Queries\UserQueryBuilder;
use Illuminate\Database\Eloquent\Builder;

class TelemetryLogQueryBuilder extends Builder
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
        return $this->whereHas('robot.zone.users', fn (UserQueryBuilder $query) => $query
            ->where('users.id', $userId)
        );
    }

    public function zoneId(int $zoneId): self
    {
        return $this->whereHas('robot', fn (RobotQueryBuilder $query) => $query
            ->where('robots.zone_id', $zoneId)
        );
    }

    /*
     * Filters
     */

    public function search(string $search): self
    {
        return $this->where(fn (self $query) => $query
            ->whereHas('robot', fn (Builder $robotQuery) => $robotQuery
                ->where('robots.name', 'like', "%{$search}%")
                ->orWhere('robots.mac_address', 'like', "%{$search}%")
            )
            ->orWhereRelation('robot.zone', 'name', 'like', "%{$search}%")
        );
    }
}
