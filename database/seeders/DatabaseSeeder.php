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
            ['email' => 'admin@ecopulse.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'role' => Role::SUPERADMIN,
            ],
        );

        $operator = User::query()->firstOrCreate(
            ['email' => 'operator@ecopulse.com'],
            [
                'name' => 'Zone Operator',
                'password' => 'password',
                'role' => Role::OPERATOR,
            ],
        );

        $firstZone = Zone::query()->firstOrCreate(
            ['name' => 'Kyiv Center'],
            [
                'center_lat' => 50.450290,
                'center_lng' => 30.510342,
                'default_zoom' => 15,
                'bounding_box' => [
                    ['lat' => 50.45529088104832, 'lng' => 30.506072044372562],
                    ['lat' => 50.45425258245089, 'lng' => 30.505943298339847],
                    ['lat' => 50.44845955100403, 'lng' => 30.492725372314457],
                    ['lat' => 50.44638263116152, 'lng' => 30.49435615539551],
                    ['lat' => 50.442228517995694, 'lng' => 30.520234107971195],
                    ['lat' => 50.44865084166792, 'lng' => 30.522551536560062],
                    ['lat' => 50.45269509182581, 'lng' => 30.527958869934086],
                    ['lat' => 50.45835099698138, 'lng' => 30.518260002136234],
                ],
            ],
        );

        $secondZone = Zone::query()->firstOrCreate(
            ['name' => 'Kyiv Politechnic Institute'],
            [
                'center_lat' => 50.449539,
                'center_lng' => 30.460035,
                'default_zoom' => 15,
                'bounding_box' => [
                    ['lat' => 50.45329623463427, 'lng' => 30.453007221221927],
                    ['lat' => 50.44672423608348, 'lng' => 30.453114509582523],
                    ['lat' => 50.44578140050939, 'lng' => 30.462620258331302],
                    ['lat' => 50.44668324362304, 'lng' => 30.467061996459964],
                    ['lat' => 50.44884213155844, 'lng' => 30.466053485870365],
                    ['lat' => 50.451041909716814, 'lng' => 30.46671867370606],
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

    }
}
