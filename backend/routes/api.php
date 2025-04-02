<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\AirlineController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Public routes
Route::post('auth/register', [UserController::class, 'store']);
Route::post('auth/login', [UserController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::put('users/{user}/edit', [UserController::class, 'update'])->name('users.edit');
    Route::post('auth/logout', [UserController::class, 'logout']);
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('user.delete');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Enterprise routes
    Route::group(['prefix' => 'enterprise', 'middleware' => 'enterprise'], function () {
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Enterprise dashboard'
            ]);
        });
    });
});

// Public airline routes - specifying explicit route names
Route::get('airlines', [AirlineController::class, 'index'])->name('airlines.index');
Route::get('airlines/{airline}', [AirlineController::class, 'show'])->name('airlines.show');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('airlines', [AirlineController::class, 'store'])->name('airlines.store');
    Route::put('airlines/{airline}', [AirlineController::class, 'update'])->name('airlines.update');
    Route::delete('airlines/{airline}', [AirlineController::class, 'destroy'])->name('airlines.destroy');
});

// Enterprise-specific airline routes
Route::group(['prefix' => 'enterprise', 'middleware' => ['auth:sanctum', 'enterprise']], function () {
    Route::get('airlines', [AirlineController::class, 'index'])->name('enterprise.airlines.index');
});
