<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Seat\SeatController;;

Route::group(['prefix' => 'dashboard/seat-map'], function () {
    // GET Seat-Map Route
    Route::get('/', [SeatController::class, 'index']);
});