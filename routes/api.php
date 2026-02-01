<?php

use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::apiResource('posts', PostController::class);

    // Admin-only route (uses Gate - prevents non-admins from accessing)
    Route::get('/admin', function () {
        return response()->json(['message' => 'Welcome to admin panel']);
    })->middleware('can:access-admin-panel');
});
