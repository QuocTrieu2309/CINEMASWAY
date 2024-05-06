<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CinemaScreen\CinemaScreenController;


Route::group(['prefix' => 'dashboard/seat_types'], function () {
    // GET Seat Type Route
    Route::get('/', [CinemaScreenController::class, 'index']);
    // GET Seat Type By Id Route
    Route::get('/{id}', [CinemaScreenController::class, 'show']);
    //Create Seat Type Route
    Route::post('/create', [CinemaScreenController::class, 'store']);
    //  Update Seat Type Route
    Route::put('/update/{id}', [CinemaScreenController::class, 'update']);
    //  Delete Seat Type Route
    Route::delete('/delete/{id}', [CinemaScreenController::class, 'destroy']);
});



