<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    /**
     * Handle user login and token creation
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Create a new token
            $token = $user->createToken('auth-token')->plainTextToken;
            
            // Calculate token expiration time
            $expirationTime = now()->addMinutes(config('sanctum.expiration', 10));

            return response()->json([
                'user' => $user,
                'token' => $token,
                'token_expires_at' => $expirationTime->toDateTimeString(),
                'token_valid_for' => '10 minutes'
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), ['email' => $request->email]);
            throw $e;
        }
    }

    /**
     * Handle user registration and token creation
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'user_type' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:255',
            ]);

            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'user_type' => $validated['user_type'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? '', // Provide default empty string
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $token = $user->createToken('auth-token')->plainTextToken;
            
            // Calculate token expiration time
            $expirationTime = now()->addMinutes(config('sanctum.expiration', 10));

            return response()->json([
                'user' => $user,
                'token' => $token,
                'token_expires_at' => $expirationTime->toDateTimeString(),
                'token_valid_for' => '10 minutes'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), ['email' => $request->email ?? null]);
            throw $e;
        }
    }

    /**
     * Revoke the user's current token
     */
    public function logout(Request $request)
    {
        if ($request->user()) {

            $request->user()->currentAccessToken()->delete();
            
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function show(User $user)
    {
        return $user;
    }

    public function update(Request $request, User $user)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z\d])/|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Get validated data
        $validatedData = $validator->validated();

        // Handle password separately
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        // Update the user with validated data
        $user->update($validatedData);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }

    /**
     * Delete the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Find the user by ID
            $user = User::find($id);

            // Check if user exists
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Get authenticated user
            $authUser = Auth::user();
            
            // Check if user is authenticated
            if (!Auth::check() || !$authUser) {
                return response()->json(['message' => 'Unauthorized access'], 401);
            }

            // Asegurarse que el authUser->id y el user->id son integers para comparación segura
            $authUserId = (int)$authUser->id;
            $userId = (int)$user->id;

            // Check if user is trying to delete their own account (usando ===)
            $isOwnAccount = $authUserId === $userId;
            
            // Agregar logging para depuración
            Log::debug("Auth user ID: $authUserId, Target user ID: $userId, Is own account: " . ($isOwnAccount ? 'true' : 'false'));
            
            // Only allow users to delete their own account
            if (!$isOwnAccount) {
                return response()->json(['message' => 'Unauthorized to delete this user'], 403);
            }

            // Delete user tokens first to prevent authentication issues
            $user->tokens()->delete();
            
            // Delete the user
            $deleted = $user->delete();
            
            // Log deletion for debugging
            Log::info("User deletion attempted: ID=$id, Success=" . ($deleted ? 'true' : 'false'));
            
            if (!$deleted) {
                return response()->json(['message' => 'Failed to delete user'], 500);
            }

            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('User deletion error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error deleting user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
