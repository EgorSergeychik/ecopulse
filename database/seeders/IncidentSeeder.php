<?php

namespace Database\Seeders;

use Domain\Incident\Enums\IncidentOperator;
use Domain\Incident\Models\Incident;
use Domain\Robot\Models\Robot;
use Domain\User\Models\User;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    public function __construct(
        private readonly int $count = 12,
        private readonly int $daysBack = 7,
        private readonly int $resolvedPercent = 50,
        private readonly ?array $types = null,
    ) {}

    public function run(): void
    {
        $robots = Robot::query()->with('zone.users')->get();

        if ($robots->isEmpty()) {
            return;
        }

        $rules = collect(config('incidents.rules', []))
            ->only($this->types ?? array_keys(config('incidents.rules', [])))
            ->all();

        if ($rules === []) {
            return;
        }

        $records = [];

        for ($index = 0; $index < $this->count; $index++) {
            $robot = $robots->random();
            $type = array_rand($rules);
            $rule = $rules[$type];
            $operator = IncidentOperator::from($rule['operator']);
            $createdAt = now()->subMinutes(fake()->numberBetween(30, $this->daysBack * 24 * 60));
            $actualValue = $this->actualValue($rule, $operator);
            $isResolved = fake()->numberBetween(1, 100) <= $this->resolvedPercent;
            $resolvedAt = $isResolved
                ? (clone $createdAt)->addMinutes(fake()->numberBetween(15, 18 * 60))
                : null;
            $resolver = $isResolved
                ? ($robot->zone->users->random() ?? User::query()->inRandomOrder()->first())
                : null;

            $snapshotData = [
                'coords' => [
                    'lat' => $this->coordinateOffset((float) $robot->zone->center_lat, 0.0022, 6),
                    'lng' => $this->coordinateOffset((float) $robot->zone->center_lng, 0.0028, 6),
                ],
                'metrics' => [
                    str($rule['field'])->after('metrics.')->value() => $actualValue,
                ],
                'threshold' => $rule['value'],
                'operator' => $operator->value,
            ];

            $records[] = [
                'robot_id' => $robot->id,
                'zone_id' => $robot->zone_id,
                'type' => $type,
                'description' => $this->descriptionFor($type, $rule, $operator, $actualValue),
                'snapshot_data' => json_encode($snapshotData, JSON_THROW_ON_ERROR),
                'severity' => $rule['severity'],
                'resolved_at' => $resolvedAt,
                'resolved_by' => $resolver?->id,
                'resolution_note' => $isResolved ? fake()->sentence(10) : null,
                'created_at' => $createdAt,
                'updated_at' => $resolvedAt ?? $createdAt,
            ];
        }

        Incident::query()->insert($records);
    }

    private function actualValue(array $rule, IncidentOperator $operator): float|int
    {
        return match ($operator) {
            IncidentOperator::LOWER => fake()->randomFloat(2, max(0, $rule['value'] - 8), max(0.5, $rule['value'] - 0.2)),
            IncidentOperator::HIGHER => fake()->randomFloat(2, $rule['value'] + 1, $rule['value'] + max(10, $rule['value'] * 0.35)),
        };
    }

    private function descriptionFor(string $type, array $rule, IncidentOperator $operator, float|int $actualValue): string
    {
        $direction = $operator === IncidentOperator::LOWER ? 'below' : 'above';

        return sprintf(
            '%s %s threshold %s (actual: %s).',
            str($type)->replace('_', ' ')->title(),
            $direction,
            $rule['value'],
            $actualValue,
        );
    }

    private function coordinateOffset(float $origin, float $delta, int $precision): float
    {
        return round($origin + fake()->randomFloat($precision, -$delta, $delta), $precision);
    }
}
