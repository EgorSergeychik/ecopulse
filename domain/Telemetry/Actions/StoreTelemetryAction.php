<?php

namespace Domain\Telemetry\Actions;

use Domain\Incident\Enums\IncidentOperator;
use Domain\Incident\Enums\IncidentSeverity;
use Domain\Incident\Models\Incident;
use Domain\Robot\Models\Robot;
use Domain\Robot\States\Active;
use Domain\Robot\States\Error;
use Domain\Robot\States\Offline;
use Domain\Telemetry\DTO\StoreTelemetryData;
use Domain\Telemetry\Events\TelemetryLogStored;
use Domain\Telemetry\Models\TelemetryLog;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StoreTelemetryAction
{
    public function __invoke(Robot $robot, StoreTelemetryData $data): array
    {
        [$telemetryLog, $incidents] = DB::transaction(function () use ($robot, $data) {
            $metrics    = $data->metrics;
            $batteryPct = (float) data_get($metrics, 'battery_pct');
            $recordedAt = $data->recorded_at ?? now();

            if ((float) $robot->battery_pct !== $batteryPct) {
                $robot->battery_pct = $batteryPct;
            }

            if ($robot->status->equals(Offline::class)) {
                $robot->status->transitionTo(Active::class);
            } elseif ($robot->isDirty('battery_pct')) {
                $robot->save();
            }

            $telemetryLog = TelemetryLog::create([
                'robot_id'    => $robot->id,
                'lat'         => $data->lat,
                'lng'         => $data->lng,
                'metrics'     => $metrics,
                'recorded_at' => $recordedAt,
            ]);

            $openIncidentTypes = Incident::where('robot_id', $robot->id)
                ->whereNull('resolved_at')
                ->pluck('type')
                ->flip()
                ->all();

            $incidents = $this->resolveIncidents(
                $robot, $data, $metrics, $batteryPct, $recordedAt, $telemetryLog, $openIncidentTypes
            );

            return [$telemetryLog, $incidents];
        });

        TelemetryLogStored::dispatch($telemetryLog, $robot);

        return [$telemetryLog, $incidents];
    }

    private function resolveIncidents(
        Robot              $robot,
        StoreTelemetryData $data,
        array              $metrics,
        float              $batteryPct,
        CarbonInterface    $recordedAt,
        TelemetryLog       $telemetryLog,
        array              $openIncidentTypes
    ): Collection {
        return collect(config('incidents.rules', []))
            ->map(function (array $rule, string $type) use (
                $metrics, $robot, $data, $batteryPct, $recordedAt, $telemetryLog, $openIncidentTypes
            ) {
                $operator    = IncidentOperator::from($rule['operator']);
                $severity    = IncidentSeverity::from($rule['severity']);
                $actualValue = data_get(['metrics' => $metrics], $rule['field']);

                if (! is_numeric($actualValue) || ! $this->matchesRule((float) $actualValue, $operator, (float) $rule['value'])) {
                    return null;
                }

                if (array_key_exists($type, $openIncidentTypes)) {
                    return null;
                }

                if (! $robot->status->equals(Error::class)) {
                    $robot->status->transitionTo(Error::class);
                }

                return Incident::create([
                    'robot_id'      => $robot->id,
                    'zone_id'       => $robot->zone_id,
                    'type'          => $type,
                    'description'   => $this->buildDescription($type, (float) $actualValue, $operator, (float) $rule['value']),
                    'severity'      => $severity,
                    'snapshot_data' => [
                        'telemetry_log_id' => $telemetryLog->id,
                        'coords'           => ['lat' => $data->lat, 'lng' => $data->lng],
                        'battery_pct'      => $batteryPct,
                        'metrics'          => $metrics,
                        'trigger'          => [
                            'field'     => $rule['field'],
                            'operator'  => $operator->value,
                            'threshold' => (float) $rule['value'],
                            'actual'    => (float) $actualValue,
                        ],
                        'recorded_at' => $recordedAt->toDateTimeString(),
                    ],
                ]);
            })
            ->filter()
            ->values();
    }

    private function matchesRule(float $actual, IncidentOperator $operator, float $threshold): bool
    {
        return match ($operator) {
            IncidentOperator::HIGHER => $actual > $threshold,
            IncidentOperator::LOWER  => $actual < $threshold,
        };
    }

    private function buildDescription(string $type, float $actual, IncidentOperator $operator, float $threshold): string
    {
        $direction = __('misc.incidents.directions.'.($operator === IncidentOperator::HIGHER ? 'above' : 'below'));

        return __('misc.incidents.types.'.$type).' '.
            __('misc.incidents.messages.threshold_breach', [
                'direction' => $direction,
                'threshold' => $threshold,
                'actual'    => $actual,
            ]);
    }
}
