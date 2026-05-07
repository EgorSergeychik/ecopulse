<?php

namespace Domain\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use Domain\Incident\Actions\GetAllIncidentsAction;
use Domain\Incident\DTO\IncidentIndexData;
use Domain\Incident\Models\Incident;
use Domain\Incident\Resources\IncidentListResource;
use Domain\Robot\Actions\GetAllRobotsAction;
use Domain\Robot\DTO\RobotIndexData;
use Domain\Robot\Models\Robot;
use Domain\Robot\Resources\RobotListResource;
use Inertia\Response;

class GetDashboardController extends Controller
{
    public function __construct(
        private readonly GetAllRobotsAction $getAllRobots,
        private readonly GetAllIncidentsAction $getAllIncidents,
    ) {}

    public function __invoke(): Response
    {
        $problemStatuses = ['error', 'maintenance'];

        $problemRobots = ($this->getAllRobots)(RobotIndexData::from(['statuses' => $problemStatuses]))
            ->limit(6)->get();

        $recentIncidents = ($this->getAllIncidents)(IncidentIndexData::from(['unresolved' => true]))
            ->limit(8)->get();

        return inertia('Dashboard', [
            'stats' => [
                'total_robots'         => Robot::query()->checkAccess()->count(),
                'active_robots'        => Robot::query()->checkAccess()->where('status', 'active')->count(),
                'problem_robots'       => Robot::query()->checkAccess()->whereIn('status', $problemStatuses)->count(),
                'unresolved_incidents' => Incident::query()->checkAccess()->whereNull('resolved_at')->count(),
            ],
            'problem_robots'   => RobotListResource::collection($problemRobots)->resolve(),
            'recent_incidents' => IncidentListResource::collection($recentIncidents)->resolve(),
            'incident_trend'   => Incident::query()->checkAccess()
                ->selectRaw('DATE(created_at) as date, severity, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('date', 'severity')
                ->orderBy('date')
                ->get()
                ->map(fn ($row) => [
                    'date'     => $row->date,
                    'severity' => $row->severity->value,
                    'count'    => $row->count,
                ]),
            'severity_counts'  => Incident::query()->checkAccess()
                ->whereNull('resolved_at')
                ->selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->get()
                ->mapWithKeys(fn ($row) => [$row->severity->value => $row->count]),
        ]);
    }
}
