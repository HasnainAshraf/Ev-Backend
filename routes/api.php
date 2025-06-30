<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public routes
Route::get('/stations', [BookingController::class, 'getStations']);
Route::get('/ports/{portId}/availability', [BookingController::class, 'getPortAvailability']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User booking routes
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/my-bookings', [BookingController::class, 'myBookings']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
    
    // Station management routes
    Route::post('/stations', [StationController::class, 'store']);
    Route::put('/stations/{id}', [StationController::class, 'update']);
    Route::delete('/stations/{id}', [StationController::class, 'destroy']);
    Route::post('/stations/{id}/ports', [StationController::class, 'addPort']);
    Route::put('/stations/{stationId}/ports/{portId}', [StationController::class, 'updatePort']);
    Route::delete('/stations/{stationId}/ports/{portId}', [StationController::class, 'deletePort']);
});