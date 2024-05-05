<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SeatType\SeatTypeController;

Route::group(['prefix' => 'dashboard/seat_types'], function () {
    // GET Seat Type Route
    Route::get('/', [SeatTypeController::class, 'index']);
    // GET Seat Type By Id Route
    Route::get('/{id}', [SeatTypeController::class, 'show']);
    //Create Seat Type Route
    Route::post('/create', [SeatTypeController::class, 'store']);
    //  Update Seat Type Route
    Route::put('/update/{id}', [SeatTypeController::class, 'update']);
    //  Delete Seat Type Route
    Route::delete('/delete/{id}', [SeatTypeController::class, 'destroy']);
});
