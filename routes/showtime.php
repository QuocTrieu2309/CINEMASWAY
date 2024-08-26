<?php

use App\Http\Controllers\API\Showtime\ShowtimeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dashboard/showtime'], function () {
    // GET All Showtime  Route
    Route::get('/', [ShowtimeController::class, 'index']);
    // GET One Showtime  Route
    Route::get('/{id}', [ShowtimeController::class, 'show']);
    //Create Showtime  Route
    Route::post('/create', [ShowtimeController::class, 'store']);
    //  Update Showtime  Route
    Route::put('/update/{id}', [ShowtimeController::class, 'update']);
    //  Delete Showtime  Route
    Route::delete('/delete/{id}', [ShowtimeController::class, 'destroy']);
});
