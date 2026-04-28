<?php

namespace Domain\Zone\Queries;

use Illuminate\Database\Eloquent\Builder;

class ZoneQueryBuilder extends Builder
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
    return $this->whereHas('users', fn ($query) => $query
        ->where('users.id', $userId)
    );
    }
}
