<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TokenAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration and token creation.
     */
    public function test_user_can_register_and_get_token(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '123-456-7890', // Add phone field
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);
    }

    /**
     * Test user login and token creation.
     */
    public function test_user_can_login_and_get_token(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'phone' => '123-456-7890', // Add phone field
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);
    }

    /**
     * Test protected route access with token.
     */
    public function test_protected_route_requires_token(): void
    {
        // Try to access protected route without token
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        // Create a user with token
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Access with token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertStatus(200);
    }

    /**
     * Test token revocation (logout).
     */
    public function test_user_can_revoke_token(): void
    {
        // Create a user with token
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Store token parts for later verification
        $tokenParts = explode('|', $token);
        $tokenId = $tokenParts[0];

        // Verify token works
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');
        $response->assertStatus(200);

        // Revoke token (logout)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');
        $response->assertStatus(200);

        // Reset the application instance to clear any cached authentication
        $this->refreshApplication();
        
        // Verify the token was actually deleted from the database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
        
        // Try using revoked token with a fresh request
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');
        $response->assertStatus(401);
    }

    /**
     * Test user deletion functionality.
     */
    public function test_user_can_delete_their_own_account(): void
    {
        // Create a user
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Send delete request
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/users/{$user->id}");
            
        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);
            
        // Verify user is deleted
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        
        // Verify token is also deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
        ]);
    }
    
    /**
     * Test unauthorized user deletion attempt.
     */
    public function test_user_cannot_delete_other_users(): void
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
}
