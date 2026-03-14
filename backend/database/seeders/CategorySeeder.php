<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Motorcycle', 'description' => 'Standard motorcycles for daily commute', 'status' => 'active'],
            ['name' => 'Scooter', 'description' => 'Scooters for easy city riding', 'status' => 'active'],
            ['name' => 'Electric Bike', 'description' => 'Electric bicycles and bikes', 'status' => 'active'],
            ['name' => 'Car', 'description' => 'Cars for rent', 'status' => 'active'],
            ['name' => 'Truck', 'description' => 'Pickup trucks and cargo vehicles', 'status' => 'active'],
            ['name' => 'Van', 'description' => 'Passenger vans and cargo vans', 'status' => 'active'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['name' => $category['name']],
                $category
            );
        }
    }
}