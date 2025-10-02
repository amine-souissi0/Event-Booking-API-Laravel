<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| This file defines your API endpoints. We’re starting with auth (Sanctum).
| We’ll add Events/Tickets/Bookings endpoints right after we verify auth works.
*/

// ---- Public (no token required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ---- Protected (token required)
Route::middleware('auth:sanctum')->group(function () {
    // basic auth utilities
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/events',            [EventController::class, 'index']);
    Route::get('/events/{event}',    [EventController::class, 'show']);
    Route::post('/events',           [EventController::class, 'store'])->middleware('role:organizer,admin');
    Route::put('/events/{event}',    [EventController::class, 'update'])->middleware('role:organizer,admin');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->middleware('role:admin');
    // Tickets under events
    Route::get('/events/{event}/tickets',  [TicketController::class, 'index']);
    Route::post('/events/{event}/tickets', [TicketController::class, 'store'])->middleware('role:organizer,admin');

// Single ticket routes
    Route::get('/tickets/{ticket}',        [TicketController::class, 'show']);
    Route::put('/tickets/{ticket}',        [TicketController::class, 'update'])->middleware('role:organizer,admin');
    Route::delete('/tickets/{ticket}',     [TicketController::class, 'destroy'])->middleware('role:organizer,admin');


    // Bookings
    Route::get('/bookings',           [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings',          [BookingController::class, 'store'])->middleware('prevent.double.booking');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
    
    
    Route::post('/bookings/{booking}/pay', [PaymentController::class, 'pay']);

    
});
