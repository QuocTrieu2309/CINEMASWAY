<?php

use App\Http\Controllers\API\Vnpay\VnpayController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'pay/vnpay'], function () {
    // GET All User Route
    Route::post('/', [VnpayController::class, 'index']);
    Route::post('/send', [VnpayController::class, 'send']);
});
