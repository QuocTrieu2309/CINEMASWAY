<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Ticket\TicketController;

Route::group(['prefix' => 'dashboard/ticket'], function () {
    // GET Ticket Route
    Route::get('/', [TicketController::class, 'index']);
    // GET Ticket By Id Route
    Route::get('/{id}', [TicketController::class, 'show']);
    //Create Ticket Route
    Route::post('/create', [TicketController::class, 'store']);
    //  Update Ticket Route
    Route::put('/update/{id}', [TicketController::class, 'update']);
    //  Delete Ticket Route
    Route::delete('/delete/{id}', [TicketController::class, 'destroy']);
});
