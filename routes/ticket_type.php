<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TicketType\TicketTypeController;


Route::group(['prefix' => 'dashboard/ticket-type'], function () {
    // GET All Ticket_Type  Route
    Route::get('/', [TicketTypeController::class, 'index']);
    // GET One Ticket_Type  Route
    Route::get('/{id}', [TicketTypeController::class, 'show']);
    //Create Ticket_Type  Route
    Route::post('/create', [TicketTypeController::class, 'store']);
    //  Update Ticket_Type  Route
    Route::put('/update/{id}', [TicketTypeController::class, 'update']);
    //  Delete Ticket_Type  Route
    Route::delete('/delete/{id}', [TicketTypeController::class, 'destroy']);
});
