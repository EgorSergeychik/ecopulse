<?php

namespace Domain\Incident\Actions;

use Domain\Incident\Models\Incident;

class ResolveIncidentAction
{
    public function __invoke(Incident $incident): Incident
    {
        $incident->update([
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return $incident->refresh();
    }
}
