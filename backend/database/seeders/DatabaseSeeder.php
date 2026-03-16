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
        \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+855 12 345 678',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_verified' => true,
        ]);

        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '+855 12 345 679',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_verified' => true,
        ]);

        \App\Models\User::create([
            'name' => 'Shop Owner',
            'email' => 'shop@example.com',
            'phone' => '+855 12 345 680',
            'password' => Hash::make('shop123'),
            'role' => 'shop_owner',
            'is_verified' => true,
        ]);

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
    }
}

