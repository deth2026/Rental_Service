<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Banteay Meanchey', 'latitude' => 13.7119, 'longitude' => 103.1375, 'status' => 'active'],
            ['name' => 'Battambang', 'latitude' => 13.1026, 'longitude' => 103.1982, 'status' => 'active'],  
            ['name' => 'Kampong Cham', 'latitude' => 11.9939, 'longitude' => 105.4543, 'status' => 'active'],
            ['name' => 'Kampong Chhnang', 'latitude' => 12.2512, 'longitude' => 104.6671, 'status' => 'active'],
            ['name' => 'Kampong Speu', 'latitude' => 11.4500, 'longitude' => 104.5167, 'status' => 'active'],
            ['name' => 'Kampong Thom', 'latitude' => 12.7117, 'longitude' => 104.8986, 'status' => 'active'],
            ['name' => 'Kampot', 'latitude' => 10.6104, 'longitude' => 104.1810, 'status' => 'active'],
            ['name' => 'Kandal', 'latitude' => 11.4167, 'longitude' => 104.9500, 'status' => 'active'],
            ['name' => 'Kep', 'latitude' => 10.4868, 'longitude' => 104.3203, 'status' => 'active'],
            ['name' => 'Koh Kong', 'latitude' => 11.6201, 'longitude' => 102.9841, 'status' => 'active'],
            ['name' => 'Kratie', 'latitude' => 12.4886, 'longitude' => 106.0179, 'status' => 'active'],
            ['name' => 'Mondulkiri', 'latitude' => 12.4625, 'longitude' => 107.2319, 'status' => 'active'],
            ['name' => 'Oddar Meanchey', 'latitude' => 14.1667, 'longitude' => 103.5000, 'status' => 'active'],
            ['name' => 'Pailin', 'latitude' => 12.9037, 'longitude' => 102.6634, 'status' => 'active'],
            ['name' => 'Phnom Penh', 'latitude' => 11.5564, 'longitude' => 104.9282, 'status' => 'active'],
            ['name' => 'Preah Sihanouk', 'latitude' => 10.6097, 'longitude' => 103.5295, 'status' => 'active'],
            ['name' => 'Preah Vihear', 'latitude' => 14.3889, 'longitude' => 104.6833, 'status' => 'active'],
            ['name' => 'Prey Veng', 'latitude' => 11.0667, 'longitude' => 105.3167, 'status' => 'active'],
            ['name' => 'Pursat', 'latitude' => 12.5386, 'longitude' => 103.9197, 'status' => 'active'],
            ['name' => 'Ratanakiri', 'latitude' => 13.7333, 'longitude' => 106.9833, 'status' => 'active'],
            ['name' => 'Siem Reap', 'latitude' => 13.3540, 'longitude' => 103.8530, 'status' => 'active'],
            ['name' => 'Stung Treng', 'latitude' => 13.5228, 'longitude' => 106.0176, 'status' => 'active'],
            ['name' => 'Svay Rieng', 'latitude' => 11.0833, 'longitude' => 105.8000, 'status' => 'active'],
            ['name' => 'Takeo', 'latitude' => 10.9833, 'longitude' => 104.7833, 'status' => 'active'],
            ['name' => 'Tbong Khmum', 'latitude' => 11.6167, 'longitude' => 105.5167, 'status' => 'active'],
        ];

        City::truncate(); // Clear existing

        foreach ($cities as $cityData) {
            City::updateOrCreate(
                ['name' => $cityData['name']],
                $cityData
            );
        }
    }
}

