<?php

namespace App\Console\Commands;

use Domain\Robot\Models\Robot;
use Domain\Telemetry\Actions\StoreTelemetryAction;
use Domain\Telemetry\DTO\StoreTelemetryData;
use Domain\Zone\Models\Zone;
use Illuminate\Console\Command;

class GenerateTelemetryCommand extends Command
{
    protected $signature = 'telemetry:generate
                            {robotId : The ID of the robot}
                            {--count=10 : Number of telemetry logs to generate}
                            {--delay=0 : Delay in seconds between logs}';

    protected $description = 'Generate fake telemetry logs for a robot and broadcast events';

    public function handle(StoreTelemetryAction $action): int
    {
        $robot = Robot::query()->with('zone')->find($this->argument('robotId'));

        if (!$robot) {
            $this->error("Robot with ID [{$this->argument('robotId')}] not found.");
            return self::FAILURE;
        }

        $count = max(1, (int) $this->option('count'));
        $delay = max(0, (int) $this->option('delay'));

        $this->info("Generating {$count} telemetry log(s) for robot [{$robot->name}] in zone [{$robot->zone->name}]...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            [$lat, $lng] = $this->randomCoordinate($robot->zone);

            $metrics = collect(config('telemetry.metrics'))
                ->map(function (array $def): float|int {
                    $f = $def['fake'];
                    return $f['type'] === 'float'
                        ? fake()->randomFloat($f['decimals'] ?? 2, $f['min'], $f['max'])
                        : fake()->numberBetween($f['min'], $f['max']);
                })
                ->all();

            $action($robot, new StoreTelemetryData(
                lat: $lat,
                lng: $lng,
                metrics: $metrics,
            ));

            $bar->advance();

            if ($delay > 0 && $i < $count - 1) {
                sleep($delay);
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done. All telemetry logs stored and events broadcasted.');

        return self::SUCCESS;
    }

    /** @return array{float, float} */
    private function randomCoordinate(Zone $zone): array
    {
        $polygon = $zone->bounding_box;

        if (empty($polygon) || count($polygon) < 3) {
            return [
                round((float) $zone->center_lat + fake()->randomFloat(6, -0.0018, 0.0018), 6),
                round((float) $zone->center_lng + fake()->randomFloat(6, -0.0024, 0.0024), 6),
            ];
        }

        $lats = array_column($polygon, 'lat');
        $lngs = array_column($polygon, 'lng');
        $minLat = min($lats);
        $maxLat = max($lats);
        $minLng = min($lngs);
        $maxLng = max($lngs);

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $lat = fake()->randomFloat(8, $minLat, $maxLat);
            $lng = fake()->randomFloat(8, $minLng, $maxLng);

            if ($this->isPointInPolygon($lat, $lng, $polygon)) {
                return [$lat, $lng];
            }
        }

        return [(float) $zone->center_lat, (float) $zone->center_lng];
    }

    private function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $yi = (float) $polygon[$i]['lat'];
            $xi = (float) $polygon[$i]['lng'];
            $yj = (float) $polygon[$j]['lat'];
            $xj = (float) $polygon[$j]['lng'];

            if ((($yi > $lat) !== ($yj > $lat)) && ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
