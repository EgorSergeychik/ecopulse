<?php

namespace Domain\Incident\Actions;

use Domain\Incident\DTO\IncidentIndexData;
use Domain\Incident\Models\Incident;
use Domain\Incident\Queries\IncidentQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAllIncidentsAction
{
    public function __invoke(IncidentIndexData $data): LengthAwarePaginator
    {
        return Incident::query()
            ->checkAccess()
            ->when($data->search, fn (IncidentQueryBuilder $query) => $query->search($data->search))
            ->with(['robot:id,name', 'zone:id,name'])
            ->latest()
            ->paginate($data->limit)
            ->withQueryString();
    }
}
