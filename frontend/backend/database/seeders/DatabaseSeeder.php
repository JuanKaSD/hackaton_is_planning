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
        // Create client users
        User::factory(5)->create();
        
        // Create enterprise users
        User::factory(3)->enterprise()->create();

        // Create test users with known credentials
        User::factory()->create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'user_type' => 'client',
        ]);
        
        User::factory()->create([
            'name' => 'Test Enterprise',
            'email' => 'enterprise@example.com',
            'user_type' => 'enterprise',
        ]);
    }
}
