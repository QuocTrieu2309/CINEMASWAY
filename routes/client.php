<?php

use App\Http\Controllers\API\Client\ChooseSeatController;
use App\Http\Controllers\API\Client\ClientController;
use Illuminate\Support\Facades\Route;
//Post tạo mới booking
Route::post('/create-booking',[ClientController::class, 'createBooking']);
//Post tạo mới booking
Route::post('/create-booking-service',[ClientController::class, 'createBooingService']);
//Post hiển thị danh sách ghế
Route::post('/show-seat-map', [ChooseSeatController::class, 'showSeatMap']);
//Post chọn ghế
Route::post('/status', [ChooseSeatController::class, 'updateStatusSeat']);
//Post hủy ghế
Route::post('/cancel', [ChooseSeatController::class, 'cancel']);
