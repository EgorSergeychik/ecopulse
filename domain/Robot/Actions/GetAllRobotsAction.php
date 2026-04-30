<?php

namespace Domain\Robot\Actions;

use Domain\Robot\DTO\RobotIndexData;
use Domain\Robot\Models\Robot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAllRobotsAction
{
    public function __invoke(RobotIndexData $data): LengthAwarePaginator
    {
        return Robot::query()
            ->checkAccess()
            ->when($data->search, fn ($query) => $query->search($data->search))
            ->with('zone')
            ->latest()
            ->paginate($data->limit)
            ->withQueryString();
    }
}
