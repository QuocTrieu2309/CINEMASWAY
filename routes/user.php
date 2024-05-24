<?php

use App\Http\Controllers\API\User\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dashboard/user'], function () {
        // GET User Route
        Route::get('/',[UserController::class,'index']);
});