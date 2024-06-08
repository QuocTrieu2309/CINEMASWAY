<?php

use App\Http\Controllers\API\Client\Movie\MovieController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'client/movie'], function () {
    //Get movie
    Route::get('/',[MovieController::class, "index"]);
    //Get detail movie
    Route::get('/{id}',[MovieController::class, "show"]);
});
