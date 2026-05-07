<?php

namespace Database\Seeders;

use Domain\Incident\Enums\IncidentOperator;
use Domain\Incident\Enums\IncidentSeverity;
use Domain\Robot\Models\Robot;
use Domain\Telemetry\Models\TelemetryLog;
use Illuminate\Database\Seeder;

class TelemetryLogSeeder extends Seeder
{
    public function __construct(
        private readonly int $countPerRobot = 24,
        private readonly int $intervalMinutes = 30,
        private readonly int $hoursBack = 24,
        private readonly bool $includeAlertSpikes = true,
    ) {}

    public function run(): void
    {
        $robots = Robot::query()->with('zone')->get();

        if ($robots->isEmpty()) {
            return;
        }

        foreach ($robots as $robot) {
            $records = [];
            $baseTime = now()->subHours($this->hoursBack);
            $latestBattery = null;

            for ($index = 0; $index < $this->countPerRobot; $index++) {
                $recordedAt = (clone $baseTime)->addMinutes($index * $this->intervalMinutes);
                $metrics = $this->buildMetrics($index);
                $latestBattery = $metrics['battery_pct'];

                $records[] = [
                    'robot_id'    => $robot->id,
                    'lat'         => $this->coordinateOffset((float) $robot->zone->center_lat, 0.0018, 6),
                    'lng'         => $this->coordinateOffset((float) $robot->zone->center_lng, 0.0024, 6),
                    'metrics'     => json_encode($metrics, JSON_THROW_ON_ERROR),
                    'recorded_at' => $recordedAt,
                    'created_at'  => $recordedAt,
                    'updated_at'  => $recordedAt,
                ];
            }

            TelemetryLog::query()->insert($records);

            $robot->update(['battery_pct' => $latestBattery]);
        }
    }

    private function buildMetrics(int $index): array
    {
        $metrics = collect(config('telemetry.metrics'))
            ->map(function (array $def, string $key) use ($index): float|int {
                if ($key === 'battery_pct') {
                    return $this->batteryValue($index);
                }
                $f = $def['fake'];
                return $f['type'] === 'float'
                    ? fake()->randomFloat($f['decimals'] ?? 2, $f['min'], $f['max'])
                    : fake()->numberBetween($f['min'], $f['max']);
            })
            ->all();

        if ($this->includeAlertSpikes) {
            $this->applySpikes($metrics, $index);
        }

        return $metrics;
    }

    private function applySpikes(array &$metrics, int $index): void
    {
        $warningRules = collect(config('incidents.rules'))
            ->filter(fn($r) => $r['severity'] === IncidentSeverity::WARNING->value)
            ->groupBy(fn($r) => str($r['field'])->after('metrics.')->value());

        // Periodically spike metrics that have a "higher" threshold
        $higherRules = $warningRules->filter(
            fn($rules) => $rules->first()['operator'] === IncidentOperator::HIGHER->value,
        );

        if ($higherRules->isNotEmpty() && $index > 0) {
            $period = (int) round($this->countPerRobot / $higherRules->count());
            if ($period > 0 && $index % $period === 0) {
                $key = $higherRules->keys()->get((intdiv($index, $period) - 1) % $higherRules->count());
                if ($key !== null && array_key_exists($key, $metrics)) {
                    $threshold = $higherRules[$key]->first()['value'];
                    $metrics[$key] = fake()->randomFloat(2, $threshold * 1.05, $threshold * 1.4);
                }
            }
        }

        // Spike "lower" metrics (e.g. battery) near the end of the sequence
        $lowerRules = $warningRules->filter(
            fn($rules) => $rules->first()['operator'] === IncidentOperator::LOWER->value,
        );

        if ($lowerRules->isNotEmpty() && $index >= $this->countPerRobot - 3) {
            foreach ($lowerRules as $key => $rules) {
                if (array_key_exists($key, $metrics)) {
                    $threshold = $rules->first()['value'];
                    $metrics[$key] = fake()->randomFloat(2, max(0, $threshold * 0.4), max(0.5, $threshold * 0.95));
                }
            }
        }
    }

    private function batteryValue(int $index): float
    {
        $f = config('telemetry.metrics.battery_pct.fake');
        $drainPerRecord = ($f['max'] - $f['min']) / max($this->countPerRobot, 1);
        $baseline = $f['max'] - ($index * $drainPerRecord);

        return round(max($f['min'], $baseline + fake()->randomFloat(2, -4, 3)), 2);
    }

    private function coordinateOffset(float $origin, float $delta, int $precision): float
    {
        return round($origin + fake()->randomFloat($precision, -$delta, $delta), $precision);
    }
}
