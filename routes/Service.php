<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Service\ServiceController;;

Route::group(['prefix' => 'dashboard/service'], function () {
    // GET service Route
    Route::get('/', [ServiceController::class, 'index']);
    // GET service  By Id Route
    Route::get('/{id}', [ServiceController::class, 'show']);
    //Create service  Route
    Route::post('/create', [ServiceController::class, 'store']);
    //  Update service  Route
    Route::put('/update/{id}', [ServiceController::class, 'update']);
    //  Delete service Route
    Route::delete('/delete/{id}', [ServiceController::class, 'destroy']);
});



