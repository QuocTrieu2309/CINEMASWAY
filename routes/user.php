<?php

use App\Http\Controllers\API\User\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dashboard/user'], function () {
    // GET All User Route
    Route::get('/',[UserController::class,'index']);
    // GET One User Route
    Route::get('/{id}',[UserController::class,'show']);
    // Create User Route
    Route::post('/create',[UserController::class,'store']);
    // Update User Route
    Route::put('/update/{id}',[UserController::class,'update']);
});