<?php

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handleNotification'])
    ->middleware('throttle:60,1');

Route::get('/availability/room', [AvailabilityController::class, 'room'])
    ->middleware('throttle:60,1');
Route::get('/availability/hall', [AvailabilityController::class, 'hall'])
    ->middleware('throttle:60,1');

