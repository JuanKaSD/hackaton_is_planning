<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test client user
        User::factory()->create([
            'name' => 'client',
            'email' => 'client@example.com',
            'user_type' => 'client',
        ]);
        
        // Create a test enterprise user
        User::factory()->create([
            'name' => 'enterprise',
            'email' => 'enterprise@example.com',
            'user_type' => 'enterprise',
        ]);
        
        // Seed airports
        $this->call([
            AirportSeeder::class,
        ]);
    }
}
