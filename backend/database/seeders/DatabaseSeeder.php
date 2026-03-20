<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // Create test users
        \App\Models\User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '+855 12 345 678',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_verified' => true,
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '+855 12 345 679',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_verified' => true,
            ]
        );

        $shopOwner = \App\Models\User::updateOrCreate(
            ['email' => 'shop@example.com'],
            [
                'name' => 'Shop Owner',
                'phone' => '+855 12 345 680',
                'password' => Hash::make('shop123'),
                'role' => 'shop_owner',
                'is_verified' => true,
            ]
        );

        // Create a test user if not exists (from feature/setting.user)
        \App\Models\User::updateOrCreate(
            ['email' => 'test2@example.com'],
            [
                'name' => 'Test User 2',
                'phone' => '+1234567890',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_verified' => true,
            ]
        );

<<<<<<< HEAD
        // Create sample categories
        \App\Models\Category::create([
            'name' => 'Cars',
            'description' => 'Passenger vehicles for personal use',
            'status' => 'active'
        ]);
=======
        $categories = [
            [
                'name' => 'Cars',
                'description' => 'Passenger vehicles for personal use',
                'status' => 'active',
            ],
            [
                'name' => 'Motorcycles',
                'description' => 'Two-wheeled vehicles for urban mobility',
                'status' => 'active',
            ],
            [
                'name' => 'Trucks',
                'description' => 'Commercial and cargo vehicles',
                'status' => 'active',
            ],
        ];
>>>>>>> e284bb084929d3720b9b75fc95ec25fb849a472e

        foreach ($categories as $category) {
            \App\Models\Category::updateOrCreate(['name' => $category['name']], $category);
        }

        $cities = [
            ['name' => 'Phnom Penh', 'status' => 'active'],
            ['name' => 'Siem Reap', 'status' => 'active'],
            ['name' => 'Battambang', 'status' => 'active'],
            ['name' => 'Sihanoukville', 'status' => 'active'],
        ];

        foreach ($cities as $city) {
            \App\Models\City::updateOrCreate(['name' => $city['name']], $city);
        }

        $shopDefinitions = [
            [
                'name' => 'Phnom Penh City Rides',
                'address' => 'Street 178, Daun Penh, Phnom Penh',
                'phone' => '+855 12 345 678',
                'status' => 'active',
                'img_url' => 'https://images.unsplash.com/photo-1590674899484-d5640e854abe?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Siem Reap Wheels',
                'address' => 'Wat Bo Road, Siem Reap',
                'phone' => '+855 77 222 111',
                'status' => 'active',
                'img_url' => 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80',
            ],
            [
                'name' => 'Battambang Motion Hub',
                'address' => 'National Road 5, Battambang',
                'phone' => '+855 98 444 555',
                'status' => 'active',
                'img_url' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=600&q=80',
            ],
        ];

        $shopMap = [];
        foreach ($shopDefinitions as $definition) {
            $shop = \App\Models\Shop::updateOrCreate(
                ['name' => $definition['name']],
                $definition + ['owner_id' => $shopOwner->id]
            );
            $shopMap[$definition['name']] = $shop;
        }

<<<<<<< HEAD
        \App\Models\City::create([
            'name' => 'Sihanoukville'
        ]);
=======
        $vehicleDefinitions = [
            [
                'shop' => 'Phnom Penh City Rides',
                'name' => 'Honda PCX 160',
                'brand' => 'Honda',
                'model' => 'PCX 160',
                'type' => 'bike',
                'transmission' => 'Auto',
                'fuel_type' => 'Petrol',
                'seats' => 2,
                'price_per_day' => 14,
                'status' => 'available',
                'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'shop' => 'Phnom Penh City Rides',
                'name' => 'Toyota Corolla Cross',
                'brand' => 'Toyota',
                'model' => 'Corolla Cross',
                'type' => 'car',
                'transmission' => 'Auto',
                'fuel_type' => 'Hybrid',
                'seats' => 5,
                'price_per_day' => 48,
                'status' => 'available',
                'image_url' => 'https://images.unsplash.com/photo-1549399542-7e82138bc3f8?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'shop' => 'Siem Reap Wheels',
                'name' => 'Yamaha NMAX 155',
                'brand' => 'Yamaha',
                'model' => 'NMAX 155',
                'type' => 'bike',
                'transmission' => 'Auto',
                'fuel_type' => 'Petrol',
                'seats' => 2,
                'price_per_day' => 12,
                'status' => 'available',
                'image_url' => 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'shop' => 'Siem Reap Wheels',
                'name' => 'Ford Everest',
                'brand' => 'Ford',
                'model' => 'Everest',
                'type' => 'car',
                'transmission' => 'Auto',
                'fuel_type' => 'Diesel',
                'seats' => 7,
                'price_per_day' => 62,
                'status' => 'available',
                'image_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'shop' => 'Battambang Motion Hub',
                'name' => 'Giant Escape 3',
                'brand' => 'Giant',
                'model' => 'Escape 3',
                'type' => 'bicycle',
                'transmission' => 'Manual',
                'fuel_type' => 'Electric',
                'seats' => 1,
                'price_per_day' => 8,
                'status' => 'available',
                'image_url' => 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1000&q=80',
            ],
        ];

        foreach ($vehicleDefinitions as $vehicleDefinition) {
            $shopName = $vehicleDefinition['shop'];
            $shop = $shopMap[$shopName] ?? null;

            if (!$shop) {
                continue;
            }

            $data = $vehicleDefinition;
            unset($data['shop']);

            \App\Models\Vehicle::updateOrCreate(
                ['name' => $data['name'], 'shop_id' => $shop->id],
                $data + ['shop_id' => $shop->id]
            );
        }
>>>>>>> e284bb084929d3720b9b75fc95ec25fb849a472e
    }
}
