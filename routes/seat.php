<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Seat\SeatController;;

Route::group(['prefix' => 'dashboard/SeatController_types'], function () {
    // GET Seat Type Route
    Route::get('/', [SeatController::class, 'index']);
    // GET Seat Type By Id Route
    Route::get('/{id}', [SeatController::class, 'show']);
    //Create Seat Type Route
    Route::post('/create', [SeatController::class, 'store']);
    //  Update Seat Type Route
    Route::put('/update/{id}', [SeatController::class, 'update']);
    //  Delete Seat Type Route
    Route::delete('/delete/{id}', [SeatController::class, 'destroy']);
});



