<?php

namespace Domain\Webots\Actions;

use Illuminate\Support\Facades\Cache;

class GenerateHotspotsAction
{
    private const HOTSPOT_COUNT = 3;
    private const TTL_HOURS = 24;

    public function execute(int $zoneId, float $south, float $west, float $north, float $east): array
    {
        return Cache::remember(
            "webots_controller_hotspots_{$zoneId}",
            now()->addHours(self::TTL_HOURS),
            function () use ($south, $west, $north, $east) {
                return $this->generate($south, $west, $north, $east);
            }
        );
    }

    private function generate(float $south, float $west, float $north, float $east): array
    {
        $metrics = config('telemetry.metrics');
        $hotspots = [];

        foreach ($metrics as $name => $cfg) {
            if ($name === 'battery_pct') {
                continue;
            }

            $fake = $cfg['fake'];
            $range = $fake['max'] - $fake['min'];
            $hotspots[$name] = [];

            for ($i = 0; $i < self::HOTSPOT_COUNT; $i++) {
                $lat = $south + lcg_value() * ($north - $south);
                $lng = $west + lcg_value() * ($east - $west);
                $value = $fake['min'] + $range * (0.75 + lcg_value() * 0.25);

                if ($fake['type'] === 'int') {
                    $value = (int) round($value);
                } else {
                    $decimals = $fake['decimals'] ?? 2;
                    $value = round($value, $decimals);
                }

                $hotspots[$name][] = ['lat' => $lat, 'lng' => $lng, 'value' => $value];
            }
        }

        return $hotspots;
    }
}
