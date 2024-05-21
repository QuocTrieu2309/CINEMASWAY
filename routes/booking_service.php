<?php

use App\Http\Controllers\Api\BookingService\BookingServiceControlle;
use Illuminate\Support\Facades\Route;
Route::group(['prefix' => 'dashboard/booking-service'], function () {
    // GET booking-service Route
    Route::get('/', [BookingServiceControlle::class, 'index']);
    // GET booking-service  By Id Route
    Route::get('/{id}', [BookingServiceControlle::class, 'show']);
    //  Update booking-service  Route
    Route::put('/update/{id}', [BookingServiceControlle::class, 'update']);
    //  Delete booking-service Route
    Route::delete('/delete/{id}', [BookingServiceControlle::class, 'destroy']);
});



