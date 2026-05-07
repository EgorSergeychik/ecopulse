<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        (new TelemetryLogSeeder(
            countPerRobot: 36,
            intervalMinutes: 20,
            hoursBack: 12,
            includeAlertSpikes: true,
        ))->run();

        (new IncidentSeeder(
            count: 14,
            daysBack: 10,
            resolvedPercent: 55,
        ))->run();
    }
}
