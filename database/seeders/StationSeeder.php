<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;
use App\Models\Port;

class StationSeeder extends Seeder
{
    public function run()
    {
        // Create sample stations
        $stations = [
            [
                'name' => 'Downtown Charging Station',
                'address' => '123 Main Street, Downtown',
                'location' => 'Downtown',
                'is_active' => true,
            ],
            [
                'name' => 'Mall Parking Charging Station',
                'address' => '456 Shopping Center, Mall District',
                'location' => 'Mall District',
                'is_active' => true,
            ],
            [
                'name' => 'Airport Charging Station',
                'address' => '789 Airport Road, Airport Area',
                'location' => 'Airport Area',
                'is_active' => true,
            ],
        ];

        foreach ($stations as $stationData) {
            $station = Station::create($stationData);

            // Create ports for each station
            for ($i = 1; $i <= 4; $i++) {
                Port::create([
                    'station_id' => $station->id,
                    'port_number' => "P{$i}",
                    'type' => 'Type 2',
                    'power_kw' => 22,
                    'is_active' => true,
                ]);
            }
        }
    }
} 