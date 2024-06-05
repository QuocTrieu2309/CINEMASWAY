<?php

use App\Http\Controllers\API\Client\Movie\MovieController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'client/movie'], function () {
    //Get movie
    Route::post('/',[MovieController::class, "index"]);
});
