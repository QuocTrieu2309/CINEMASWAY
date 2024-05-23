<?php

use App\Http\Controllers\Api\Booking\BookingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dashboard/booking'], function () {
    // GET booking Route
    Route::get('/', [BookingController::class, 'index']);
    // GET One booking  Route
    Route::get('/{id}', [BookingController::class, 'show']);
    // GET update booking  Route
    Route::put('/update/{id}', [BookingController::class, 'update']);
    //  Delete booking  Route
    Route::delete('/delete/{id}', [BookingController::class, 'destroy']);
});