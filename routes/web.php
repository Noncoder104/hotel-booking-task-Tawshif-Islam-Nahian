<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

// 1. Initial form
Route::get('/', [BookingController::class, 'create'])->name('booking.create');

// 2. Submits the form and shows prices
Route::post('/search', [BookingController::class, 'search'])->name('booking.search');

// 3. Confirms and stores the booking
Route::post('/confirm', [BookingController::class, 'store'])->name('booking.store');

// 4. The final Thank You page
Route::get('/thankyou/{booking}', [BookingController::class, 'thankyou'])->name('booking.thankyou');

//Route::get('/', function () {
  //  return view('welcome');
//});
