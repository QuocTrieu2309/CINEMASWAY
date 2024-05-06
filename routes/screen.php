<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Screen\ScreenController;;

Route::group(['prefix' => 'dashboard/seat_types'], function () {
    // GET Seat Type Route
    Route::get('/', [ScreenController::class, 'index']);
    // GET Seat Type By Id Route
    Route::get('/{id}', [ScreenController::class, 'show']);
    //Create Seat Type Route
    Route::post('/create', [ScreenController::class, 'store']);
    //  Update Seat Type Route
    Route::put('/update/{id}', [ScreenController::class, 'update']);
    //  Delete Seat Type Route
    Route::delete('/delete/{id}', [ScreenController::class, 'destroy']);
});



