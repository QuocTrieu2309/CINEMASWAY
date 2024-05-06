<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\cinema\CinemaController;;

Route::group(['prefix' => 'dashboard/seat_types'], function () {
    // GET Seat Type Route
    Route::get('/', [CinemaController::class, 'index']);
    // GET Seat Type By Id Route
    Route::get('/{id}', [CinemaController::class, 'show']);
    //Create Seat Type Route
    Route::post('/create', [CinemaController::class, 'store']);
    //  Update Seat Type Route
    Route::put('/update/{id}', [CinemaController::class, 'update']);
    //  Delete Seat Type Route
    Route::delete('/delete/{id}', [CinemaController::class, 'destroy']);
});



