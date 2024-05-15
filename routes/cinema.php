<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\cinema\CinemaController;;

Route::group(['prefix' => 'dashboard/cinema'], function () {
    // GET Cinema Route
    Route::get('/', [CinemaController::class, 'index']);
    // GET Cinema By Id Route
    Route::get('/{id}', [CinemaController::class, 'show']);
    //Create  Cinema Route
    Route::post('/create', [CinemaController::class, 'store']);
    //  Update  Cinema Route
    Route::put('/update/{id}', [CinemaController::class, 'update']);
    //  Delete Seat Type Cinema Route
    Route::delete('/delete/{id}', [CinemaController::class, 'destroy']);
});



