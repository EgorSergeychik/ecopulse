<?php

namespace Domain\Incident\Controllers;

use App\Http\Controllers\Controller;
use Domain\Incident\Actions\GetAllIncidentsAction;
use Domain\Incident\DTO\IncidentIndexData;
use Domain\Incident\Resources\IncidentListResource;
use Illuminate\Http\Request;

class GetAllIncidentsController extends Controller
{
    public function __construct(
        private readonly GetAllIncidentsAction $getAllIncidents,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = IncidentIndexData::fromRequest($request);
        $incidents = ($this->getAllIncidents)($data);

        return inertia('Incidents', [
            'incidents' => IncidentListResource::collection($incidents),
            'filters' => [
                'search' => $data->search,
            ],
        ]);
    }
}
