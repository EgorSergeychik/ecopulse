<?php

namespace Database\Seeders;

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
        private readonly array $metricRanges = [
            'battery_min' => 8,
            'battery_max' => 96,
            'co2_min' => 420,
            'co2_max' => 980,
            'noise_min' => 38,
            'noise_max' => 82,
        ],
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
                $battery = $this->batteryValue($index);
                $co2 = $this->metricValue('co2_min', 'co2_max');
                $noiseLevel = $this->metricValue('noise_min', 'noise_max');

                if ($this->includeAlertSpikes && $index > 0 && $index % 9 === 0) {
                    $co2 = fake()->numberBetween(1050, 1550);
                }

                if ($this->includeAlertSpikes && $index > 0 && $index % 13 === 0) {
                    $noiseLevel = fake()->numberBetween(86, 104);
                }

                if ($this->includeAlertSpikes && $index >= $this->countPerRobot - 3) {
                    $battery = fake()->randomFloat(2, 4, 9.8);
                }

                $latestBattery = $battery;

                $records[] = [
                    'robot_id' => $robot->id,
                    'lat' => $this->coordinateOffset((float) $robot->zone->center_lat, 0.0018, 6),
                    'lng' => $this->coordinateOffset((float) $robot->zone->center_lng, 0.0024, 6),
                    'metrics' => json_encode([
                        'battery_pct' => $battery,
                        'co2' => $co2,
                        'noise_level' => $noiseLevel,
                    ], JSON_THROW_ON_ERROR),
                    'recorded_at' => $recordedAt,
                    'created_at' => $recordedAt,
                    'updated_at' => $recordedAt,
                ];
            }

            TelemetryLog::query()->insert($records);

            $robot->update([
                'battery_pct' => $latestBattery,
            ]);
        }
    }

    private function batteryValue(int $index): float
    {
        $min = $this->metricRanges['battery_min'];
        $max = $this->metricRanges['battery_max'];
        $drainPerRecord = ($max - $min) / max($this->countPerRobot, 1);
        $baseline = $max - ($index * $drainPerRecord);

        return round(max($min, $baseline + fake()->randomFloat(2, -4, 3)), 2);
    }

    private function metricValue(string $minKey, string $maxKey): int
    {
        return fake()->numberBetween(
            $this->metricRanges[$minKey],
            $this->metricRanges[$maxKey],
        );
    }

    private function coordinateOffset(float $origin, float $delta, int $precision): float
    {
        return round($origin + fake()->randomFloat($precision, -$delta, $delta), $precision);
    }
}
