<?php

use App\Http\Controllers\API\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'account'], function () {
    // Register Route
    Route::post('/register',[AuthController::class,'register'])->name('account.register');
});
