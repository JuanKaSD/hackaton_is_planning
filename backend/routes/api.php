<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::middleware([EnsureFrontendRequestsAreStateful::class, 'throttle:api'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::put('users/{user}/edit', [UserController::class, 'update'])->name('users.edit');
});

Route::post('auth/register', [UserController::class, 'store']);
Route::post('auth/login', [UserController::class, 'login']);
