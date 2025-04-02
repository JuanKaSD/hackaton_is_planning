<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user deletion - self deletion
     */
    public function test_user_can_delete_their_own_account(): void
    {
        // Create a user
        $user = User::factory()->client()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Send delete request
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/users/{$user->id}");
            
        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);
            
        // Verify user is deleted
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
    
    /**
     * Test user deletion - enterprise cannot delete other users
     */
    public function test_enterprise_cannot_delete_other_users(): void
    {
        // Create an enterprise user
        $enterprise = User::factory()->enterprise()->create();
        $token = $enterprise->createToken('test-token')->plainTextToken;
        
        // Create a regular user to delete
        $user = User::factory()->client()->create();
        
        // Send delete request as enterprise
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/users/{$user->id}");
            
        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized to delete this user']);
            
        // Verify user is not deleted
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
    
    /**
     * Test user deletion - client user cannot delete other users
     */
    public function test_client_cannot_delete_other_users(): void
    {
        // Create two users
        $user1 = User::factory()->client()->create();
        $user2 = User::factory()->client()->create();
        $token = $user1->createToken('test-token')->plainTextToken;
        
        // Send delete request using user1's token to delete user2
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/users/{$user2->id}");
            
        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized to delete this user']);
            
        // Verify user2 is not deleted
        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }
    
    /**
     * Test user deletion - cannot delete non-existent user
     */
    public function test_cannot_delete_nonexistent_user(): void
    {
        // Create a user
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Try to delete non-existent user
        $nonExistentId = 9999;
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/users/{$nonExistentId}");
            
        $response->assertStatus(404)
            ->assertJson(['message' => 'User not found']);
    }
    
    /**
     * Test user deletion - unauthenticated attempt
     */
    public function test_unauthenticated_user_cannot_delete(): void
    {
        // Create a user
        $user = User::factory()->create();
        
        // Try to delete without authentication
        $response = $this->deleteJson("/api/users/{$user->id}");
            
        $response->assertStatus(401);
        
        // Verify user is not deleted
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
