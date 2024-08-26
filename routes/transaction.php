<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Transaction\TransactionController;

Route::group(['prefix' => 'dashboard/transaction'], function () {
    // GET transaction Route
    Route::get('/', [TransactionController::class, 'index']);
    // GET transaction By Id Route
    Route::get('/{id}', [TransactionController::class, 'show']);
    //  Update transaction Route
    Route::put('/update/{id}', [TransactionController::class, 'update']);
    //  Delete transaction Route
    Route::delete('/delete/{id}', [TransactionController::class, 'destroy']);
});
