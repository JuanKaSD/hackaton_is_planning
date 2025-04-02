<?php

namespace Tests\Unit;

use App\Models\Airline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_airline()
    {
        // Create an enterprise user
        $enterprise = User::factory()->create([
            'user_type' => 'enterprise'
        ]);

        // Create an airline owned by the enterprise
        $airline = Airline::factory()->create([
            'enterprise_id' => $enterprise->id
        ]);

        // Assert airline was created successfully
        $this->assertModelExists($airline);
        $this->assertEquals($enterprise->id, $airline->enterprise_id);
    }

    public function test_airline_belongs_to_enterprise()
    {
        // Create an enterprise user
        $enterprise = User::factory()->create([
            'user_type' => 'enterprise'
        ]);

        // Create an airline owned by the enterprise
        $airline = Airline::factory()->create([
            'enterprise_id' => $enterprise->id
        ]);

        // Test the relationship works correctly
        $this->assertInstanceOf(User::class, $airline->enterprise);
        $this->assertEquals($enterprise->id, $airline->enterprise->id);
    }
    
    public function test_can_update_airline()
    {
        // Create an airline
        $airline = Airline::factory()->create();
        
        // Update the airline
        $newName = 'Updated Airline Name';
        $airline->update(['name' => $newName]);
        
        // Assert the update was successful
        $this->assertEquals($newName, $airline->fresh()->name);
    }
    
    public function test_can_delete_airline()
    {
        // Create an airline
        $airline = Airline::factory()->create();
        
        // Get the ID for later verification
        $airlineId = $airline->id;
        
        // Delete the airline
        $airline->delete();
        
        // Assert it's been deleted
        $this->assertModelMissing($airline);
        $this->assertDatabaseMissing('airlines', ['id' => $airlineId]);
    }
}
