<?php

namespace Domain\Incident\Controllers;

use App\Http\Controllers\Controller;
use Domain\Incident\Actions\ResolveIncidentAction;
use Domain\Incident\Models\Incident;
use Illuminate\Http\RedirectResponse;

class ResolveIncidentController extends Controller
{
    public function __construct(
        private readonly ResolveIncidentAction $resolveIncident,
    ) {}

    public function __invoke(Incident $incident): RedirectResponse
    {
        ($this->resolveIncident)($incident);

        return back();
    }
}
