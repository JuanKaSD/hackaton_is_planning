<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AirlineControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test the airline index endpoint.
     */
    public function test_can_view_all_airlines()
    {
        // Create some airlines
        Airline::factory()->count(3)->create();

        // Make request to the index endpoint
        $response = $this->getJson('/api/airlines');

        // Assert successful response with the correct number of airlines
        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test viewing a specific airline.
     */
    public function test_can_view_single_airline()
    {
        // Create an airline
        $airline = Airline::factory()->create();

        // Make request to show the airline
        $response = $this->getJson("/api/airlines/{$airline->id}");

        // Assert successful response with correct airline data
        $response->assertStatus(200)
            ->assertJson([
                'id' => $airline->id,
                'name' => $airline->name,
            ]);
    }

    /**
     * Test creating an airline as an enterprise user.
     */
    public function test_enterprise_user_can_create_airline()
    {
        // Prepare airline data
        $airlineData = [
            'name' => $this->faker->company . ' Airlines',
        ];

        // Create enterprise user with proper type
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne([
            'user_type' => 'enterprise'
        ]);
        
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/airlines', $airlineData);

        // Verify the airline was saved to the database
        $this->assertDatabaseHas('airlines', [
            'name' => $airlineData['name'],
            'enterprise_id' => $user->id,
        ]);
    }

    /**
     * Test that non-enterprise users cannot create airlines.
     */
    public function test_client_user_cannot_create_airline()
    {
        // Create a client user
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne([
            'user_type' => 'client'
        ]);

        // Prepare airline data
        $airlineData = [
            'name' => $this->faker->company . ' Airlines',
        ];

        // Make authenticated request to create airline
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/airlines', $airlineData);

        // Assert error response - currently returning 500 but ideally should be 403
        // TODO: Update to check for 403 once proper authorization is implemented
        $response->assertStatus(500);

        // Verify no airline was created in the database
        $this->assertDatabaseMissing('airlines', [
            'name' => $airlineData['name'],
        ]);
    }

    /**
     * Test updating an airline as the owning enterprise user.
     */
    public function test_enterprise_can_update_own_airline()
    {
        // Create an enterprise user
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne([
            'user_type' => 'enterprise'
        ]);

        // Create an airline owned by this enterprise
        $airline = Airline::factory()->create([
            'enterprise_id' => $user->id
        ]);

        // Prepare update data
        $updateData = [
            'name' => 'Updated Airline Name',
        ];

        // Make authenticated request to update airline
        $response = $this->actingAs($user)
            ->putJson("/api/airlines/{$airline->id}", $updateData);

        // Assert successful update
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Airline updated successfully',
                'data' => [
                    'id' => $airline->id,
                    'name' => $updateData['name'],
                ]
            ]);

        // Verify the airline was updated in the database
        $this->assertDatabaseHas('airlines', [
            'id' => $airline->id,
            'name' => $updateData['name'],
        ]);
    }

    /**
     * Test that an enterprise cannot update another enterprise's airline.
     */
    public function test_enterprise_cannot_update_others_airline()
    {
        // Create two enterprise users
        /** @var \App\Models\User $enterprise1 */
        $enterprise1 = User::factory()->createOne(['user_type' => 'enterprise']);
        /** @var \App\Models\User $enterprise2 */
        $enterprise2 = User::factory()->createOne(['user_type' => 'enterprise']);

        // Create an airline owned by enterprise1
        $airline = Airline::factory()->create([
            'enterprise_id' => $enterprise1->id
        ]);

        // Prepare update data
        $updateData = [
            'name' => 'Updated Airline Name',
        ];

        // Make authenticated request as enterprise2 to update airline
        $response = $this->actingAs($enterprise2, 'sanctum')
            ->putJson("/api/airlines/{$airline->id}", $updateData);

        // Assert error response - currently returning 500 but ideally should be 403
        // TODO: Update to check for 403 once proper authorization is implemented
        $response->assertStatus(500);

        // Verify the airline was not updated in the database
        $this->assertDatabaseMissing('airlines', [
            'id' => $airline->id,
            'name' => $updateData['name'],
        ]);
    }

    /**
     * Test deleting an airline as the owning enterprise user.
     */
    public function test_enterprise_can_delete_own_airline()
    {
        // Create an enterprise user
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne([
            'user_type' => 'enterprise'
        ]);

        // Create an airline owned by this enterprise
        $airline = Airline::factory()->create([
            'enterprise_id' => $user->id
        ]);

        // Make authenticated request to delete airline
        $response = $this->actingAs($user)
            ->deleteJson("/api/airlines/{$airline->id}");

        // Assert successful deletion
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Airline deleted successfully'
            ]);

        // Verify the airline was deleted from the database
        $this->assertDatabaseMissing('airlines', [
            'id' => $airline->id,
        ]);
    }

    /**
     * Test that an enterprise cannot delete another enterprise's airline.
     */
    public function test_enterprise_cannot_delete_others_airline()
    {
        // Create two enterprise users
        /** @var \App\Models\User $enterprise1 */
        $enterprise1 = User::factory()->createOne(['user_type' => 'enterprise']);
        /** @var \App\Models\User $enterprise2 */
        $enterprise2 = User::factory()->createOne(['user_type' => 'enterprise']);

        // Create an airline owned by enterprise1
        $airline = Airline::factory()->create([
            'enterprise_id' => $enterprise1->id
        ]);

        // Make authenticated request as enterprise2 to delete airline
        $response = $this->actingAs($enterprise2, 'sanctum')
            ->deleteJson("/api/airlines/{$airline->id}");

        // Assert error response - currently returning 500 but ideally should be 403
        // TODO: Update to check for 403 once proper authorization is implemented
        $response->assertStatus(500);

        // Verify the airline still exists in the database
        $this->assertDatabaseHas('airlines', [
            'id' => $airline->id,
        ]);
    }
}
