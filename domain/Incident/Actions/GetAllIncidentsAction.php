<?php

namespace Domain\Incident\Actions;

use Domain\Incident\DTO\IncidentIndexData;
use Domain\Incident\Models\Incident;
use Domain\Incident\Queries\IncidentQueryBuilder;

class GetAllIncidentsAction
{
    public function __invoke(IncidentIndexData $data): IncidentQueryBuilder
    {
        return Incident::query()
            ->checkAccess()
            ->when($data->search, fn (IncidentQueryBuilder $query) => $query->search($data->search))
            ->when($data->unresolved, fn (IncidentQueryBuilder $query) => $query->whereNull('resolved_at'))
            ->with(['robot:id,name', 'zone:id,name'])
            ->latest();
    }
}
