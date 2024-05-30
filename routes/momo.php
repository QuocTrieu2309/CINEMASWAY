<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Momo\MomoController;

Route::group(['prefix' => 'user/momo'], function () {

    //Create Momo Route
    Route::post('/create',[MomoController::class,'createPayment']);

});
