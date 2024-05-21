<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SeatMap\SeatMapController;

Route::group(['prefix' => 'dashboard/seat-map'], function () {
    // GET Seat-Map Route
    Route::get('/', [SeatMapController::class, 'index']);
    // GET Seat-Map By ID Route 
    Route::get('/{id}', [SeatMapController::class, 'show']);
    //Create Seat-Map Route
    Route::post('/create', [SeatMapController::class, 'store']);

});