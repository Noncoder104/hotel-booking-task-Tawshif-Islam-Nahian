<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       DB::table('room_categories')->insert([
        ['name' => 'Premium Deluxe', 'base_price' => 12000], // [cite: 8]
        ['name' => 'Super Deluxe', 'base_price' => 10000],  // [cite: 9]
        ['name' => 'Standard Deluxe', 'base_price' => 8000], // [cite: 10]
       
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
       
       // ]);
       ]);
    }
}
