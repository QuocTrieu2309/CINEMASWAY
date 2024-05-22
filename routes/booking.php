<?php

use App\Http\Controllers\Api\Booking\BookingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dashboard/booking'], function () {
    // GET booking Route
    Route::get('/', [BookingController::class, 'index']);
});