<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TicketType\TicketTypeController;


Route::group(['prefix' => 'dashboard/ticket-type'], function () {
    // GET All User-permission Route
    Route::get('/', [TicketTypeController::class, 'index']);
    // GET One User-permission Route
    Route::get('/{id}', [TicketTypeController::class, 'show']);
    //Create User-permission Route
    Route::post('/create', [TicketTypeController::class, 'store']);
    //  Update User-permission Route
    Route::put('/update/{id}', [TicketTypeController::class, 'update']);
    //  Delete User-permission Route
    Route::delete('/delete/{id}', [TicketTypeController::class, 'destroy']);
});
