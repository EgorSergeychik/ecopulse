<?php

namespace Domain\Zone\Actions;

use Domain\Zone\DTO\ZoneIndexData;
use Domain\Zone\Models\Zone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAllZonesAction
{
    public function __invoke(ZoneIndexData $data): LengthAwarePaginator
    {
        return Zone::query()
            ->checkAccess()
            ->when($data->search, fn ($query) => $query->search($data->search))
            ->with([
                'users:id',
                'media',
            ])
            ->paginate($data->limit)
            ->withQueryString();
    }
}
