<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Screen\ScreenController;;

Route::group(['prefix' => 'dashboard/screen'], function () {
    // GET Screen Route
    Route::get('/', [ScreenController::class, 'index']);
    // GET Screen By Id Route
    Route::get('/{id}', [ScreenController::class, 'show']);
    //Create Screen Route
    Route::post('/create', [ScreenController::class, 'store']);
    //  Update Screen Route
    Route::put('/update/{id}', [ScreenController::class, 'update']);
    //  Delete Screen Route
    Route::delete('/delete/{id}', [ScreenController::class, 'destroy']);
});



