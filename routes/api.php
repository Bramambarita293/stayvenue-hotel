<?php

use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handleNotification']);

