<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Seat\SeatController;;

Route::group(['prefix' => 'dashboard/seat'], function () {
    // GET Seat Route
    Route::get('/', [SeatController::class, 'index']);
    // GET Seat  By Id Route
    Route::get('/{id}', [SeatController::class, 'show']);
    //Create Seat  Route
    Route::post('/create', [SeatController::class, 'store']);
    //  Update Seat  Route
    Route::put('/update/{id}', [SeatController::class, 'update']);
    //  Delete Seat Route
    Route::delete('/delete/{id}', [SeatController::class, 'destroy']);
});



