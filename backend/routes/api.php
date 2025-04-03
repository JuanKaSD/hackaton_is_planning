<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\AirlineController;
use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\FlightController;
use App\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnterpriseMiddleware;

// Public routes
Route::post('auth/register', [UserController::class, 'store']);
Route::post('auth/login', [UserController::class, 'login']);

// Token check route
Route::middleware('auth:sanctum')->get('/check', function () {
    return response()->json([
        'status' => 'valid',
        'message' => 'Token is valid and has not expired',
    ]);
})->name('check');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::put('users/{user}/edit', [UserController::class, 'update'])->name('users.edit');
    Route::post('auth/logout', [UserController::class, 'logout']);
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('user.delete');
    Route::get('flights', [FlightController::class, 'index'])->name('flights.index');
    
    // Enterprise-only routes
    Route::middleware(EnterpriseMiddleware::class)->group(function () {
        // Airline Routes - Grouped together for better organization
        Route::get('airlines', [AirlineController::class, 'index'])->name('airlines.index');
        Route::get('airlines/{airline}', [AirlineController::class, 'show'])->name('airlines.show');
        Route::post('airlines', [AirlineController::class, 'store'])->name('airlines.store');
        Route::put('airlines/{airline}', [AirlineController::class, 'update'])->name('airlines.update');
        Route::delete('airlines/{airline}', [AirlineController::class, 'destroy'])->name('airlines.destroy');
        Route::get('enterprise/flights', [AirlineController::class, 'getEnterpriseFlights'])->name('enterprise.flights');

        // Flight Routes
        Route::get('flights/{flight}', [FlightController::class, 'show'])->name('flights.show');
        Route::post('flights', [FlightController::class, 'store'])->name('flights.store');
        Route::put('flights/{flight}', [FlightController::class, 'update'])->name('flights.update');
        Route::delete('flights/{flight}', [FlightController::class, 'destroy'])->name('flights.destroy');

        // Airport Routes
        Route::get('airports', [AirportController::class, 'index'])->name('airports.index');

        // Booking Routes - Only for clients
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    });
});
