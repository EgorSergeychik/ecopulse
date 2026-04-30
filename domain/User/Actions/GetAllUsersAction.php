<?php

namespace Domain\User\Actions;

use Domain\User\DTO\UserIndexData;
use Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAllUsersAction
{
    public function __invoke(UserIndexData $data): LengthAwarePaginator
    {
        return User::query()
            ->when($data->search, fn ($query) => $query->search($data->search))
            ->latest()
            ->paginate($data->limit)
            ->withQueryString();
    }
}
