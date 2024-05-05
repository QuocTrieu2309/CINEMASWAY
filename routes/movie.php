<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Movie\MovieController;

Route::group(['prefix' => 'dashboard/movie'], function () {
    // GET Movie Route
    Route::get('/',[MovieController::class,'index']);
    // GET Movie Route
    Route::get('/{id}',[MovieController::class,'show']);
    //Create Movie Route
    Route::post('/create',[MovieController::class,'store']);
    //  Update Movie Route
    Route::put('/update/{id}',[MovieController::class,'update']);
    //  Delete Movie Route
    Route::delete('/delete/{id}',[MovieController::class,'destroy']);
});