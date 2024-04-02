<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\ProfileController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'account'], function () {
    // Register Route
    Route::post('/register',[AuthController::class,'register']);
    // Login Route 
    Route::post('/login',[AuthController::class,'login']);
    // Logout Route
    Route::post('/logout',[AuthController::class,'logout']);
    
    // Get Profile Route
    Route::get('/profile',[ProfileController::class,'getProfile']);
});
