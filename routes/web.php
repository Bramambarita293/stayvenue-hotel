<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\RoomController;


Route::get('/', function () {
    return view('welcome');
});

// Halaman Contact & Lokasi
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');

Route::get('/rooms/{id}', [RoomController::class, 'show'])->whereNumber('id')->name('rooms.show');

// Route Detail Gedung
Route::get('/halls', [HallController::class, 'index'])->name('halls.index');

Route::get('/halls/{id}', [HallController::class, 'show'])->whereNumber('id')->name('halls.show');


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});

// route login ( user )
Route::middleware(['auth'])->group(function () {
    // booking room
    Route::post('/booking/room', [BookingController::class, 'checkoutRoom'])->middleware('throttle:10,1')->name('booking.room.checkout');
    Route::post('/booking/hall', [BookingController::class, 'checkoutHall'])->middleware('throttle:10,1')->name('booking.hall.checkout');
    Route::get('/booking/pay/{code}', [BookingController::class, 'showPaymentPage'])->where('code', '[A-Za-z0-9\-]+')->name('booking.pay');

    Route::get('/voucher/{code}', [VoucherController::class, 'show'])->where('code', '[A-Za-z0-9\-]+')->name('voucher.show');
    Route::get('/voucher/{code}/download', [VoucherController::class, 'download'])->where('code', '[A-Za-z0-9\-]+')->middleware('throttle:10,1')->name('voucher.download');

    // reservasi
    Route::get('/my-reservations', [BookingController::class, 'userReservations'])->name('user.reservations');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});
