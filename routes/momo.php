<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Momo\MomoController;

Route::group(['prefix' => 'client/momo'], function () {
    //Create Momo Route
    Route::post('/create',[MomoController::class,'payment']);
    // call back
    Route::post('/momo/callback', [MomoController::class, 'callback'])->name('momo.callback');
    // payment success
    Route::post('/success', [MomoController::class, 'checkStatusTransaction'])->name('payment.success');


});
