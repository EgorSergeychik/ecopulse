<?php

namespace Domain\Telemetry\Actions;

use Domain\Telemetry\Models\TelemetryLog;
use Domain\Zone\DTO\ZoneHeatmapSnapshotData;

class BuildZoneHeatmapSnapshotAction
{
    private const EARTH_RADIUS = 6_371_000.0;
    private const CELL_SIZE_METERS = 18.0;

    public function __invoke(ZoneHeatmapSnapshotData $data): array
    {
        $rows = TelemetryLog::query()
            ->checkAccess()
            ->zoneId($data->zone->id)
            ->whereNotNull("metrics->{$data->metric}")
            ->latest('recorded_at')
            ->limit($data->limit)
            ->get(['lat', 'lng', 'metrics', 'recorded_at']);

        return $this->buildSnapshot(
            rows: $rows,
            metric: $data->metric,
            refLat: (float) $data->zone->center_lat,
            refLng: (float) $data->zone->center_lng,
            limit: $data->limit,
        );
    }

    public function buildSnapshot(iterable $rows, string $metric, float $refLat, float $refLng, int $limit): array
    {
        $cells = [];
        $sampleCount = 0;

        foreach ($rows as $row) {
            $lat = (float) data_get($row, 'lat');
            $lng = (float) data_get($row, 'lng');
            $value = data_get($row, "metrics.{$metric}");

            if (!is_numeric($value)) {
                continue;
            }

            $value = (float) $value;
            [$x, $z] = $this->toLocal($lat, $lng, $refLat, $refLng);

            $cellX = (int) floor($x / self::CELL_SIZE_METERS);
            $cellZ = (int) floor($z / self::CELL_SIZE_METERS);
            $key = "{$cellX}:{$cellZ}";

            if (!isset($cells[$key])) {
                $cells[$key] = [
                    'sum' => 0.0,
                    'count' => 0,
                    'lat_sum' => 0.0,
                    'lng_sum' => 0.0,
                    'recorded_at' => data_get($row, 'recorded_at')?->toIso8601String(),
                ];
            }

            $cells[$key]['sum'] += $value;
            $cells[$key]['count']++;
            $cells[$key]['lat_sum'] += $lat;
            $cells[$key]['lng_sum'] += $lng;
            $cells[$key]['recorded_at'] ??= data_get($row, 'recorded_at')?->toIso8601String();

            $sampleCount++;
        }

        $points = collect($cells)
            ->map(function (array $cell) {
                $value = $cell['sum'] / $cell['count'];

                return [
                    'lat' => round($cell['lat_sum'] / $cell['count'], 7),
                    'lng' => round($cell['lng_sum'] / $cell['count'], 7),
                    'value' => round($value, 4),
                    'count' => $cell['count'],
                    'recorded_at' => $cell['recorded_at'],
                ];
            })
            ->values();

        $values = $points->pluck('value');
        $minValue = $values->min();
        $maxValue = $values->max();
        $range = max(0.000001, (float) $maxValue - (float) $minValue);

        $points = $points
            ->map(function (array $point) use ($minValue, $range) {
                $point['intensity'] = round(($point['value'] - $minValue) / $range, 4);

                return $point;
            })
            ->sortBy('value')
            ->values();

        return [
            'metric' => $metric,
            'limit' => $limit,
            'cell_size_m' => self::CELL_SIZE_METERS,
            'sample_count' => $sampleCount,
            'cell_count' => $points->count(),
            'stats' => [
                'min' => $minValue !== null ? round((float) $minValue, 4) : null,
                'max' => $maxValue !== null ? round((float) $maxValue, 4) : null,
            ],
            'points' => $points->all(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function toLocal(float $lat, float $lng, float $refLat, float $refLng): array
    {
        $x = deg2rad($lng - $refLng) * self::EARTH_RADIUS * cos(deg2rad($refLat));
        $z = deg2rad($lat - $refLat) * self::EARTH_RADIUS;

        return [$x, $z];
    }
}
