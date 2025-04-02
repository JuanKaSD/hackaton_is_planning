<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UserAuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user registration.
     */
    public function test_user_can_register()
    {
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'client',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'user_type',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ]);

        // Verify user was created in the database
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'user_type' => $userData['user_type'],
        ]);
    }

    /**
     * Test user login with correct credentials.
     */
    public function test_user_can_login_with_correct_credentials()
    {
        // Create a user - explicitly get a single model
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne([
            'password' => bcrypt($password = 'Password123!'),
            'user_type' => 'client',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user',
                'token',
            ]);
    }

    /**
     * Test user login with incorrect credentials.
     */
    public function test_user_cannot_login_with_incorrect_credentials()
    {
        // Create a user - explicitly get a single model
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne([
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test user logout.
     */
    public function test_user_can_logout()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne();

        // First login to get a token
        $loginResponse = $this->actingAs($user)->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password', // Default password from UserFactory
        ]);

        // Then logout
        $response = $this->actingAs($user)->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);
    }

    /**
     * Test enterprise user registration.
     */
    public function test_enterprise_user_can_register()
    {
        $userData = [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => $this->faker->phoneNumber(),
            'user_type' => 'enterprise',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'user_type',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ]);

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'user_type' => 'enterprise',
        ]);
    }

    /**
     * Test authentication middleware protects routes properly.
     */
    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can access their profile.
     */
    public function test_authenticated_user_can_get_their_profile()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->createOne();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    /**
     * Test the isClient and isEnterprise methods on User model.
     */
    public function test_user_helper_methods()
    {
        // Create a client user - explicitly get a single model
        /** @var \App\Models\User $clientUser */
        $clientUser = User::factory()->createOne([
            'user_type' => 'client',
        ]);

        // Create an enterprise user - explicitly get a single model
        /** @var \App\Models\User $enterpriseUser */
        $enterpriseUser = User::factory()->createOne([
            'user_type' => 'enterprise',
        ]);

        // Test isClient method
        $this->assertTrue($clientUser->isClient());
        $this->assertFalse($enterpriseUser->isClient());

        // Test isEnterprise method
        $this->assertTrue($enterpriseUser->isEnterprise());
        $this->assertFalse($clientUser->isEnterprise());
    }
}
