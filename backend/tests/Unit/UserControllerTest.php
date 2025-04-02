<?php

namespace Tests\Unit;

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Mockery;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    
    protected $controller;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new UserController();
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function index_returns_all_users()
    {
        // Create test users
        $users = User::factory()->count(3)->create();
        
        // Call index method
        $response = $this->controller->index();
        
        // Assert that all users are returned
        $this->assertCount(3, $response);
        $this->assertInstanceOf(User::class, $response->first());
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function login_returns_token_for_valid_credentials()
    {
        // Create a user with known credentials
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);
        
        // Create a request with valid credentials
        $request = new Request([
            'email' => 'test@example.com',
            'password' => 'correct-password',
        ]);
        
        // Call the login method
        $response = $this->controller->login($request);
        
        // Assert response contains user and token
        $data = $response->getData();
        $this->assertEquals($user->id, $data->user->id);
        $this->assertNotEmpty($data->token);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function login_throws_exception_for_invalid_credentials()
    {
        // Create a user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);
        
        // Create request with wrong password
        $request = new Request([
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
        
        // Expect validation exception
        $this->expectException(ValidationException::class);
        
        // Call login method
        $this->controller->login($request);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function store_creates_new_user_with_token()
    {
        // Registration data
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '123-456-7890',
        ];
        
        // Create request with registration data
        $request = new Request($userData);
        
        // Call the store method
        $response = $this->controller->store($request);
        
        // Assert user was created
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '123-456-7890',
        ]);
        
        // Assert response has user and token
        $data = $response->getData();
        $this->assertEquals('New User', $data->user->name);
        $this->assertNotEmpty($data->token);
        $this->assertEquals(201, $response->status());
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_revokes_current_token()
    {
        // Create a user with a token
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        
        // Create a request with the authenticated user
        $request = Request::create('/api/auth/logout', 'POST');
        
        // Mock the Auth facade to return our user
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);
        
        // Call logout method
        $response = $this->controller->logout($request);
        
        // Assert success message
        $data = $response->getData();
        $this->assertEquals('Logged out successfully', $data->message);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function show_returns_specified_user()
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
        ]);
        
        // Call show method
        $response = $this->controller->show($user);
        
        // Assert correct user is returned
        $this->assertEquals($user->id, $response->id);
        $this->assertEquals('Test User', $response->name);
        $this->assertEquals('testuser@example.com', $response->email);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function update_changes_user_attributes()
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        
        // Update data
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];
        
        // Create request with update data
        $request = new Request($updateData);
        
        // Call update method
        $response = $this->controller->update($request, $user);
        
        // Assert user was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
        
        // Assert response
        $this->assertEquals(200, $response->status());
        $data = $response->getData();
        $this->assertEquals('User updated successfully', $data->message);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function destroy_deletes_own_user_account()
    {
        // Create a user with client user_type
        $user = User::factory()->create(['user_type' => 'client']);
        $userId = $user->id;
        
        // Create a better mock of the authenticated user
        $mockUser = Mockery::mock('App\Models\User');
        $mockUser->shouldReceive('getAttribute')->with('id')->andReturn($userId);
        $mockUser->shouldReceive('getAttribute')->with('user_type')->andReturn('client');
        $mockUser->shouldReceive('id')->andReturn($userId);
        
        // Expect setAttribute method calls that might happen behind the scenes
        $mockUser->shouldReceive('setAttribute')->withAnyArgs()->andReturnSelf();
        
        // Add tokens method for deletion
        $tokenMock = Mockery::mock();
        $tokenMock->shouldReceive('delete')->andReturn(true);
        $mockUser->shouldReceive('tokens')->andReturn($tokenMock);
        
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn($userId);
        Auth::shouldReceive('user')->andReturn($mockUser);
        
        // Call destroy method
        $response = $this->controller->destroy($userId);
        
        // Assert response
        $this->assertEquals(200, $response->status());
        $data = $response->getData();
        $this->assertEquals('User deleted successfully', $data->message);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function enterprise_cannot_delete_other_user_accounts()
    {
        // Create enterprise and regular user
        $enterprise = User::factory()->create(['user_type' => 'enterprise']);
        $user = User::factory()->create(['user_type' => 'client']);
        
        $enterpriseId = $enterprise->id;
        $userId = $user->id;
        
        // Mock Auth for enterprise user
        $mockUser = Mockery::mock('App\Models\User');
        $mockUser->shouldReceive('getAttribute')->with('id')->andReturn($enterpriseId);
        $mockUser->shouldReceive('getAttribute')->with('user_type')->andReturn('enterprise');
        $mockUser->shouldReceive('id')->andReturn($enterpriseId);
        $mockUser->shouldReceive('setAttribute')->withAnyArgs()->andReturnSelf();
        
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn($enterpriseId);
        Auth::shouldReceive('user')->andReturn($mockUser);
        
        // Call destroy method
        $response = $this->controller->destroy($userId);
        
        // Assert response
        $this->assertEquals(403, $response->status());
        $data = $response->getData();
        $this->assertEquals('Unauthorized to delete this user', $data->message);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function client_user_cannot_delete_other_user_accounts()
    {
        // Create two regular users
        $user1 = User::factory()->create(['user_type' => 'client']);
        $user2 = User::factory()->create(['user_type' => 'client']);
        
        $user1Id = $user1->id;
        $user2Id = $user2->id;
        
        // Create a proper mock for the authenticated user
        $mockUser = Mockery::mock('App\Models\User');
        $mockUser->shouldReceive('getAttribute')->with('id')->andReturn($user1Id);
        $mockUser->shouldReceive('getAttribute')->with('user_type')->andReturn('client');
        $mockUser->shouldReceive('id')->andReturn($user1Id);
        $mockUser->shouldReceive('setAttribute')->withAnyArgs()->andReturnSelf();
        
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn($user1Id);
        Auth::shouldReceive('user')->andReturn($mockUser);
        
        // Call destroy method to delete user2
        $response = $this->controller->destroy($user2Id);
        
        // Assert response
        $this->assertEquals(403, $response->status(), "Expected 403 status code but got " . $response->status());
        $data = $response->getData();
        $this->assertEquals('Unauthorized to delete this user', $data->message);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function cannot_delete_nonexistent_user()
    {
        // Create a user
        $user = User::factory()->create(['user_type' => 'client']);
        $userId = $user->id;
        
        // Mock Auth
        $mockUser = Mockery::mock('App\Models\User');
        $mockUser->shouldReceive('getAttribute')->with('id')->andReturn($userId);
        $mockUser->shouldReceive('getAttribute')->with('user_type')->andReturn('client');
        $mockUser->shouldReceive('id')->andReturn($userId);
        $mockUser->shouldReceive('setAttribute')->withAnyArgs()->andReturnSelf();
        
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn($userId);
        Auth::shouldReceive('user')->andReturn($mockUser);
        
        // Non-existent ID
        $nonExistentId = 9999;
        
        // Call destroy with non-existent ID
        $response = $this->controller->destroy($nonExistentId);
        
        // Assert response
        $this->assertEquals(404, $response->status());
        $data = $response->getData();
        $this->assertEquals('User not found', $data->message);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
