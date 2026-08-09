<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\RoomController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

Route::get('/rooms/{id}', function ($id) {
    $roomType = \App\Models\RoomType::findOrFail($id);
    return view('rooms.show', compact('roomType'));
})->name('rooms.show');

// Route Detail Gedung
Route::get('/halls', [HallController::class, 'index'])->name('halls.index');

Route::get('/halls/{id}', function ($id) {
    $hall = \App\Models\Hall::findOrFail($id);
    $sessions = \App\Models\HallSession::all();
    return view('halls.show', compact('hall', 'sessions'));
})->name('halls.show');


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// route login ( user )
Route::middleware(['auth'])->group(function () {
    // booking room
    Route::post('/booking/room', [BookingController::class, 'checkoutRoom'])->name('booking.room.checkout');
    Route::post('/booking/hall', [BookingController::class, 'checkoutHall'])->name('booking.hall.checkout');
    Route::get('/booking/pay/{code}', [BookingController::class, 'showPaymentPage'])->name('booking.pay');

    Route::get('/voucher/{code}', [VoucherController::class, 'show'])->name('voucher.show');
    Route::get('/voucher/{code}/download', [VoucherController::class, 'download'])->name('voucher.download');

    // reservasi
    Route::get('/my-reservations', [BookingController::class, 'userReservations'])->name('user.reservations');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});
