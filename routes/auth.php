<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\PasswordResetController;
use App\Http\Controllers\API\Auth\ChangePasswordController;
use App\Http\Controllers\API\Auth\ProfileController;
use Illuminate\Support\Facades\Route;
Route::group(['prefix' => 'account'], function () {
    // Register Route
    Route::post('/register',[AuthController::class,'register']);
    // Login Route
    Route::post('/login',[AuthController::class,'login']);
    // Logout Route
    Route::post('/logout',[AuthController::class,'logout']);
    // Verify email register Route
    Route::get('/verify-email/{token}', [AuthController::class, 'verify'])->name('verify-email');

    // Get Profile Route
    Route::get('/profile',[ProfileController::class,'getProfile']);
    // POST Profile Update Route
    Route::post('/profile/update',[ProfileController::class,'updateProfile']);
    // POST forgot Password Route
    Route::post('/forgot-password',[PasswordResetController::class,'forgotPassword']);
    // POST Check-token Route
    Route::post('/check-token',[PasswordResetController::class,'checkToken']);
    //POST Active Token Route
    Route::post('/active-token',[ChangePasswordController::class,'activeToken']);
    //POST Change Password Route
    Route::post('/change-password',[ChangePasswordController::class,'changePassword']);
});
