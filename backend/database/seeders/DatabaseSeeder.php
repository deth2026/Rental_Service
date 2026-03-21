<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Customer',
                'phone' => '+85512345678',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_verified' => true,
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'phone' => '+85512345679',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_verified' => true,
            ]
        );

        $phnomPenhOwner = \App\Models\User::updateOrCreate(
            ['email' => 'phnom-owner@chongchoul.com'],
            [
                'name' => 'Phnom Penh Owner',
                'phone' => '+85510888001',
                'password' => Hash::make('shop123'),
                'role' => 'shop_owner',
                'is_verified' => true,
            ]
        );

        $siemReapOwner = \App\Models\User::updateOrCreate(
            ['email' => 'siem-owner@chongchoul.com'],
            [
                'name' => 'Siem Reap Owner',
                'phone' => '+85510888002',
                'password' => Hash::make('shop123'),
                'role' => 'shop_owner',
                'is_verified' => true,
            ]
        );

        $battambangOwner = \App\Models\User::updateOrCreate(
            ['email' => 'battambang-owner@chongchoul.com'],
            [
                'name' => 'Battambang Owner',
                'phone' => '+85510888003',
                'password' => Hash::make('shop123'),
                'role' => 'shop_owner',
                'is_verified' => true,
            ]
        );

        $categories = [
            [
                'name' => 'Cars',
                'description' => 'Cars and SUVs for city and inter-province travel.',
                'status' => 'active',
            ],
            [
                'name' => 'Motorbike',
                'description' => 'Scooters and motorbikes for daily travel.',
                'status' => 'active',
            ],
            [
                'name' => 'Bicycle',
                'description' => 'City and touring bicycles.',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::updateOrCreate(['name' => $category['name']], $category);
        }

        $cities = [
            ['name' => 'Banteay Meanchey', 'status' => 'active'],
            ['name' => 'Battambang', 'status' => 'active'],
            ['name' => 'Kampong Cham', 'status' => 'active'],
            ['name' => 'Kampong Chhnang', 'status' => 'active'],
            ['name' => 'Kampong Speu', 'status' => 'active'],
            ['name' => 'Kampong Thom', 'status' => 'active'],
            ['name' => 'Kampot', 'status' => 'active'],
            ['name' => 'Kandal', 'status' => 'active'],
            ['name' => 'Kep', 'status' => 'active'],
            ['name' => 'Koh Kong', 'status' => 'active'],
            ['name' => 'Kratie', 'status' => 'active'],
            ['name' => 'Mondulkiri', 'status' => 'active'],
            ['name' => 'Oddar Meanchey', 'status' => 'active'],
            ['name' => 'Pailin', 'status' => 'active'],
            ['name' => 'Phnom Penh', 'status' => 'active'],
            ['name' => 'Preah Sihanouk', 'status' => 'active'],
            ['name' => 'Preah Vihear', 'status' => 'active'],
            ['name' => 'Prey Veng', 'status' => 'active'],
            ['name' => 'Pursat', 'status' => 'active'],
            ['name' => 'Ratanakiri', 'status' => 'active'],
            ['name' => 'Siem Reap', 'status' => 'active'],
            ['name' => 'Stung Treng', 'status' => 'active'],
            ['name' => 'Svay Rieng', 'status' => 'active'],
            ['name' => 'Takeo', 'status' => 'active'],
            ['name' => 'Tboung Khmum', 'status' => 'active'],
        ];

        $cityMap = [];
        foreach ($cities as $city) {
            $payload = $city;
            if (Schema::hasColumn('cities', 'shop_id')) {
                $payload['shop_id'] = 0;
            }
            $record = \App\Models\City::updateOrCreate(['name' => $city['name']], $payload);
            $cityMap[$city['name']] = $record;
        }

        $shopDefinitions = [
            [
                'name' => 'CHONG CHOUL Riverside Phnom Penh',
                'city' => 'Phnom Penh',
                'owner_id' => $phnomPenhOwner->id,
                'description' => 'Central city branch near the riverfront and major hotels.',
                'address' => 'Sisowath Quay, Daun Penh, Phnom Penh, Cambodia',
                'location' => 'https://maps.google.com/?q=11.5676,104.9282',
                'phone' => '+85512911001',
                'img_url' => 'https://images.unsplash.com/photo-1525609004556-c46c7d6cf023?auto=format&fit=crop&w=900&q=80',
                'latitude' => 11.5676,
                'longitude' => 104.9282,
                'total_reviews' => 124,
                'status' => 'active',
            ],
            [
                'name' => 'CHONG CHOUL Tuol Kork Phnom Penh',
                'city' => 'Phnom Penh',
                'owner_id' => $phnomPenhOwner->id,
                'description' => 'North Phnom Penh branch for airport and suburban pickups.',
                'address' => 'Street 315, Tuol Kork, Phnom Penh, Cambodia',
                'location' => 'https://maps.google.com/?q=11.5756,104.8897',
                'phone' => '+85512911002',
                'img_url' => 'https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=900&q=80',
                'latitude' => 11.5756,
                'longitude' => 104.8897,
                'total_reviews' => 98,
                'status' => 'active',
            ],
            [
                'name' => 'CHONG CHOUL Pub Street Siem Reap',
                'city' => 'Siem Reap',
                'owner_id' => $siemReapOwner->id,
                'description' => 'City center branch near Pub Street and Old Market.',
                'address' => 'Pub Street, Svay Dangkum, Siem Reap, Cambodia',
                'location' => 'https://maps.google.com/?q=13.3549,103.8519',
                'phone' => '+85512912001',
                'img_url' => 'https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=900&q=80',
                'latitude' => 13.3549,
                'longitude' => 103.8519,
                'total_reviews' => 115,
                'status' => 'active',
            ],
            [
                'name' => 'CHONG CHOUL Angkor Gate Siem Reap',
                'city' => 'Siem Reap',
                'owner_id' => $siemReapOwner->id,
                'description' => 'Angkor-side branch ideal for temple route pickup.',
                'address' => 'Charles De Gaulle Blvd, Siem Reap, Cambodia',
                'location' => 'https://maps.google.com/?q=13.4125,103.8669',
                'phone' => '+85512912002',
                'img_url' => 'https://images.unsplash.com/photo-1583267746897-2cf415887172?auto=format&fit=crop&w=900&q=80',
                'latitude' => 13.4125,
                'longitude' => 103.8669,
                'total_reviews' => 87,
                'status' => 'active',
            ],
            [
                'name' => 'CHONG CHOUL Central Market Battambang',
                'city' => 'Battambang',
                'owner_id' => $battambangOwner->id,
                'description' => 'Main Battambang branch near Psar Nat and downtown.',
                'address' => 'Psar Nat Road, Battambang, Cambodia',
                'location' => 'https://maps.google.com/?q=13.1026,103.1982',
                'phone' => '+85512913001',
                'img_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=900&q=80',
                'latitude' => 13.1026,
                'longitude' => 103.1982,
                'total_reviews' => 92,
                'status' => 'active',
            ],
            [
                'name' => 'CHONG CHOUL Riverside Battambang',
                'city' => 'Battambang',
                'owner_id' => $battambangOwner->id,
                'description' => 'Riverfront branch with fast pickup and return service.',
                'address' => 'Riverside Road, Battambang, Cambodia',
                'location' => 'https://maps.google.com/?q=13.0962,103.1999',
                'phone' => '+85512913002',
                'img_url' => 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=900&q=80',
                'latitude' => 13.0962,
                'longitude' => 103.1999,
                'total_reviews' => 76,
                'status' => 'active',
            ],
        ];

        $shopMap = [];
        foreach ($shopDefinitions as $shopDefinition) {
            $cityName = $shopDefinition['city'];
            $city = $cityMap[$cityName] ?? null;
            $payload = $shopDefinition;
            unset($payload['city']);
            $payload['city_id'] = $city?->id;

            $shop = \App\Models\Shop::updateOrCreate(
                ['name' => $shopDefinition['name']],
                $payload
            );

            $shopMap[$shopDefinition['name']] = $shop;
        }

        $vehicleDefinitions = [
            ['shop' => 'CHONG CHOUL Riverside Phnom Penh', 'name' => 'Toyota Corolla Cross 2024', 'type' => 'car', 'brand' => 'Toyota', 'model' => 'Corolla Cross', 'plate_number' => 'PP-CC-2401', 'year' => 2024, 'price_per_day' => 55, 'fuel_type' => 'Hybrid', 'transmission' => 'Auto', 'seats' => 5, 'status' => 'Available', 'description' => 'Fuel efficient SUV for city and highway rides.', 'image_url' => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Riverside Phnom Penh', 'name' => 'Honda Click 160i', 'type' => 'moto', 'brand' => 'Honda', 'model' => 'Click 160i', 'plate_number' => 'PP-MT-2402', 'year' => 2024, 'price_per_day' => 14, 'fuel_type' => 'Petrol', 'transmission' => 'Auto', 'seats' => 2, 'status' => 'Available', 'description' => 'Comfortable automatic scooter for daily commuting.', 'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Riverside Phnom Penh', 'name' => 'Giant Escape 3', 'type' => 'bicycle', 'brand' => 'Giant', 'model' => 'Escape 3', 'plate_number' => 'PP-BI-2403', 'year' => 2025, 'price_per_day' => 8, 'fuel_type' => 'N/A', 'transmission' => 'Manual', 'seats' => 1, 'status' => 'Available', 'description' => 'Lightweight bicycle for city discovery rides.', 'image_url' => 'https://images.unsplash.com/photo-1571333250630-f0230c320b6d?auto=format&fit=crop&w=1000&q=80'],

            ['shop' => 'CHONG CHOUL Tuol Kork Phnom Penh', 'name' => 'Ford Everest Titanium', 'type' => 'car', 'brand' => 'Ford', 'model' => 'Everest', 'plate_number' => 'PP-CC-2404', 'year' => 2023, 'price_per_day' => 68, 'fuel_type' => 'Diesel', 'transmission' => 'Auto', 'seats' => 7, 'status' => 'Available', 'description' => 'Premium SUV for families and long trips.', 'image_url' => 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Tuol Kork Phnom Penh', 'name' => 'Yamaha NMAX 155', 'type' => 'moto', 'brand' => 'Yamaha', 'model' => 'NMAX 155', 'plate_number' => 'PP-MT-2405', 'year' => 2024, 'price_per_day' => 13, 'fuel_type' => 'Petrol', 'transmission' => 'Auto', 'seats' => 2, 'status' => 'Available', 'description' => 'Smooth and stable scooter for Phnom Penh roads.', 'image_url' => 'https://images.unsplash.com/photo-1622185135505-2d7950039941?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Tuol Kork Phnom Penh', 'name' => 'Trek FX 2 Disc', 'type' => 'bicycle', 'brand' => 'Trek', 'model' => 'FX 2 Disc', 'plate_number' => 'PP-BI-2406', 'year' => 2024, 'price_per_day' => 9, 'fuel_type' => 'N/A', 'transmission' => 'Manual', 'seats' => 1, 'status' => 'Available', 'description' => 'Hybrid bicycle with disc brakes and upright comfort.', 'image_url' => 'https://images.unsplash.com/photo-1544191696-15693072b5a8?auto=format&fit=crop&w=1000&q=80'],

            ['shop' => 'CHONG CHOUL Pub Street Siem Reap', 'name' => 'Hyundai Tucson 2024', 'type' => 'car', 'brand' => 'Hyundai', 'model' => 'Tucson', 'plate_number' => 'SR-CC-2407', 'year' => 2024, 'price_per_day' => 60, 'fuel_type' => 'Petrol', 'transmission' => 'Auto', 'seats' => 5, 'status' => 'Available', 'description' => 'Comfortable SUV for temple tours and city travel.', 'image_url' => 'https://images.unsplash.com/photo-1590362891991-f776e747a588?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Pub Street Siem Reap', 'name' => 'Honda ADV 160', 'type' => 'moto', 'brand' => 'Honda', 'model' => 'ADV 160', 'plate_number' => 'SR-MT-2408', 'year' => 2024, 'price_per_day' => 15, 'fuel_type' => 'Petrol', 'transmission' => 'Auto', 'seats' => 2, 'status' => 'Available', 'description' => 'Adventure scooter with comfortable suspension.', 'image_url' => 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Pub Street Siem Reap', 'name' => 'Polygon Path 3', 'type' => 'bicycle', 'brand' => 'Polygon', 'model' => 'Path 3', 'plate_number' => 'SR-BI-2409', 'year' => 2024, 'price_per_day' => 7, 'fuel_type' => 'N/A', 'transmission' => 'Manual', 'seats' => 1, 'status' => 'Available', 'description' => 'Easy city bicycle for short local rides.', 'image_url' => 'https://images.unsplash.com/photo-1529429612778-707f7f1f3f4b?auto=format&fit=crop&w=1000&q=80'],

            ['shop' => 'CHONG CHOUL Angkor Gate Siem Reap', 'name' => 'Kia Sportage 2023', 'type' => 'car', 'brand' => 'Kia', 'model' => 'Sportage', 'plate_number' => 'SR-CC-2410', 'year' => 2023, 'price_per_day' => 58, 'fuel_type' => 'Petrol', 'transmission' => 'Auto', 'seats' => 5, 'status' => 'Available', 'description' => 'Spacious SUV for Angkor routes and province drives.', 'image_url' => 'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Angkor Gate Siem Reap', 'name' => 'Suzuki Burgman 125', 'type' => 'moto', 'brand' => 'Suzuki', 'model' => 'Burgman 125', 'plate_number' => 'SR-MT-2411', 'year' => 2023, 'price_per_day' => 11, 'fuel_type' => 'Petrol', 'transmission' => 'Auto', 'seats' => 2, 'status' => 'Available', 'description' => 'City-friendly scooter with large seat comfort.', 'image_url' => 'https://images.unsplash.com/photo-1623869675781-80aa31012a5a?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Angkor Gate Siem Reap', 'name' => 'Cannondale Quick 5', 'type' => 'bicycle', 'brand' => 'Cannondale', 'model' => 'Quick 5', 'plate_number' => 'SR-BI-2412', 'year' => 2024, 'price_per_day' => 9, 'fuel_type' => 'N/A', 'transmission' => 'Manual', 'seats' => 1, 'status' => 'Available', 'description' => 'Fast fitness bicycle for city and trail mix rides.', 'image_url' => 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1000&q=80'],

            ['shop' => 'CHONG CHOUL Central Market Battambang', 'name' => 'Toyota Hilux Revo', 'type' => 'car', 'brand' => 'Toyota', 'model' => 'Hilux Revo', 'plate_number' => 'BT-CC-2413', 'year' => 2022, 'price_per_day' => 63, 'fuel_type' => 'Diesel', 'transmission' => 'Auto', 'seats' => 5, 'status' => 'Available', 'description' => 'Reliable pickup for mixed city and rural routes.', 'image_url' => 'https://images.unsplash.com/photo-1593941707882-a5bba13938c2?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Central Market Battambang', 'name' => 'Honda Wave 125', 'type' => 'moto', 'brand' => 'Honda', 'model' => 'Wave 125', 'plate_number' => 'BT-MT-2414', 'year' => 2024, 'price_per_day' => 10, 'fuel_type' => 'Petrol', 'transmission' => 'Semi-Auto', 'seats' => 2, 'status' => 'Available', 'description' => 'Classic and efficient motorbike for local roads.', 'image_url' => 'https://images.unsplash.com/photo-1611241443709-0b9f7d92dbf7?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Central Market Battambang', 'name' => 'Merida Crossway 20', 'type' => 'bicycle', 'brand' => 'Merida', 'model' => 'Crossway 20', 'plate_number' => 'BT-BI-2415', 'year' => 2024, 'price_per_day' => 7, 'fuel_type' => 'N/A', 'transmission' => 'Manual', 'seats' => 1, 'status' => 'Available', 'description' => 'Comfort bicycle for city streets and river roads.', 'image_url' => 'https://images.unsplash.com/photo-1517949908114-7217261d24d6?auto=format&fit=crop&w=1000&q=80'],

            ['shop' => 'CHONG CHOUL Riverside Battambang', 'name' => 'Mazda CX-5 2023', 'type' => 'car', 'brand' => 'Mazda', 'model' => 'CX-5', 'plate_number' => 'BT-CC-2416', 'year' => 2023, 'price_per_day' => 57, 'fuel_type' => 'Petrol', 'transmission' => 'Auto', 'seats' => 5, 'status' => 'Available', 'description' => 'Premium crossover for comfortable travel.', 'image_url' => 'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Riverside Battambang', 'name' => 'Yamaha Aerox 155', 'type' => 'moto', 'brand' => 'Yamaha', 'model' => 'Aerox 155', 'plate_number' => 'BT-MT-2417', 'year' => 2024, 'price_per_day' => 12, 'fuel_type' => 'Petrol', 'transmission' => 'Auto', 'seats' => 2, 'status' => 'Available', 'description' => 'Sporty scooter for quick and smooth city transport.', 'image_url' => 'https://images.unsplash.com/photo-1558981285-6f0c94958bb6?auto=format&fit=crop&w=1000&q=80'],
            ['shop' => 'CHONG CHOUL Riverside Battambang', 'name' => 'Scott Sub Cross 40', 'type' => 'bicycle', 'brand' => 'Scott', 'model' => 'Sub Cross 40', 'plate_number' => 'BT-BI-2418', 'year' => 2025, 'price_per_day' => 8, 'fuel_type' => 'N/A', 'transmission' => 'Manual', 'seats' => 1, 'status' => 'Available', 'description' => 'Hybrid bike designed for comfort and light adventure.', 'image_url' => 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1000&q=80'],
        ];

        foreach ($vehicleDefinitions as $vehicleDefinition) {
            $shopName = $vehicleDefinition['shop'];
            $shop = $shopMap[$shopName] ?? null;

            if (!$shop) {
                continue;
            }

            $payload = $vehicleDefinition;
            unset($payload['shop']);
            $payload['shop_id'] = $shop->id;

            \App\Models\Vehicle::updateOrCreate(
                ['plate_number' => $payload['plate_number']],
                $payload
            );
        }

        // Keep an additional customer account for quick UI testing.
        \App\Models\User::updateOrCreate(
            ['email' => 'test2@example.com'],
            [
                'name' => 'Second Test Customer',
                'phone' => '+85512345680',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_verified' => true,
            ]
        );
    }
}
