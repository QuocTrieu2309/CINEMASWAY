<?php
use App\Http\Controllers\API\Client\ClientController;
use Illuminate\Support\Facades\Route;
//Post tạo mới booking
Route::post('/create-booking',[ClientController::class, 'createBooking']);
//Post tạo mới booking
Route::post('/create-booking-service',[ClientController::class, 'createBooingService']);
