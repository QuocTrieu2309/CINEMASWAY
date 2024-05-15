<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CinemaScreen\CinemaScreenController;


Route::group(['prefix' => 'dashboard/cinema-screen'], function () {
    // GET Cinema-Screen Route
    Route::get('/', [CinemaScreenController::class, 'index']);
    // GET Cinema-Screen By Id Route
    Route::get('/{id}', [CinemaScreenController::class, 'show']);
    //Create Cinema-Screen Route
    Route::post('/create', [CinemaScreenController::class, 'store']);
    //  Update Seat TypeCinema-Screen Route
    Route::put('/update/{id}', [CinemaScreenController::class, 'update']);
    //  Delete Cinema-Screen Route
    Route::delete('/delete/{id}', [CinemaScreenController::class, 'destroy']);
});



