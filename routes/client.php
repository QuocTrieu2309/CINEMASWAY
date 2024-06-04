<?php
use App\Http\Controllers\API\Client\ClientController;
use Illuminate\Support\Facades\Route;

Route::post('booking',[ClientController::class, 'updateTickets']);
