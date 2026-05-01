<?php

namespace Database\Seeders;

use App\Support\Enums\Role;
use Domain\Robot\Enums\RobotState;
use Domain\Robot\Models\Robot;
use Domain\User\Models\User;
use Domain\Zone\Models\Zone;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'role' => Role::SUPERADMIN,
            ],
        );

        $operator = User::query()->firstOrCreate(
            ['email' => 'operator@example.com'],
            [
                'name' => 'Operations User',
                'password' => 'password',
                'role' => Role::OPERATOR,
            ],
        );

        $firstZone = Zone::query()->firstOrCreate(
            ['name' => 'Zone 1'],
            [
                'center_lat' => 50.450001,
                'center_lng' => 30.523333,
                'default_zoom' => 15,
                'bounding_box' => [
                    ['lat' => 50.46983064382773, 'lng' => 30.48843383789063],
                    ['lat' => 50.443163737694626, 'lng' => 30.476074218750004],
                    ['lat' => 50.43397984872763, 'lng' => 30.54061889648438],
                    ['lat' => 50.445131482079425, 'lng' => 30.54130554199219],
                    ['lat' => 50.448192255165054, 'lng' => 30.52482604980469],
                    ['lat' => 50.463274601689534, 'lng' => 30.518302917480472],
                ],
            ],
        );

        $secondZone = Zone::query()->firstOrCreate(
            ['name' => 'Zone 2'],
            [
                'center_lat' => 50.442200,
                'center_lng' => 30.536800,
                'default_zoom' => 15,
                'bounding_box' => [
                    ['lat' => 50.45102333461782, 'lng' => 30.5225944519043],
                    ['lat' => 50.43513592110541, 'lng' => 30.517444610595707],
                    ['lat' => 50.42846759165547, 'lng' => 30.551948547363285],
                    ['lat' => 50.44076534933065, 'lng' => 30.55830001831055],
                    ['lat' => 50.44545934648414, 'lng' => 30.550918579101566],
                    ['lat' => 50.44993494435097, 'lng' => 30.539245605468754],
                ],
            ],
        );

        $operator->zones()->syncWithoutDetaching([$firstZone->id, $secondZone->id]);
        $superAdmin->zones()->syncWithoutDetaching([$firstZone->id, $secondZone->id]);

        Robot::query()->firstOrCreate(
            ['mac_address' => '02:EC:00:00:00:11'],
            [
                'name' => 'EcoPulse Rover 11',
                'zone_id' => $firstZone->id,
                'status' => RobotState::ACTIVE->value,
                'battery_pct' => 81.50,
            ],
        );

        Robot::query()->firstOrCreate(
            ['mac_address' => '02:EC:00:00:00:12'],
            [
                'name' => 'EcoPulse Rover 12',
                'zone_id' => $firstZone->id,
                'status' => RobotState::MAINTENANCE->value,
                'battery_pct' => 47.25,
            ],
        );

        Robot::query()->firstOrCreate(
            ['mac_address' => '02:EC:00:00:00:21'],
            [
                'name' => 'EcoPulse Carrier 21',
                'zone_id' => $secondZone->id,
                'status' => RobotState::ACTIVE->value,
                'battery_pct' => 63.10,
            ],
        );

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
