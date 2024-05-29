<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Voucher\VoucherController;

Route::group(['prefix' => 'dashboard/voucher'], function () {
    // GET Voucher Route
    Route::get('/', [VoucherController::class, 'index']);
    // GET Voucher By Id Route
    Route::get('/{id}', [VoucherController::class, 'show']);
    //Create Voucher Route
    Route::post('/create', [VoucherController::class, 'store']);
    //  Update Voucher Route
    Route::put('/update/{id}', [VoucherController::class, 'update']);
    //  Delete Voucher Route
    Route::delete('/delete/{id}', [VoucherController::class, 'destroy']);
});
