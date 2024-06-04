<?php
use App\Http\Controllers\API\Client\ClientController;
use App\Http\Controllers\API\Client\FilterController;
use Illuminate\Support\Facades\Route;

Route::post('booking',[ClientController::class, 'updateTickets']);
// lấy tất cả thông tin xuất chiếu kèm rạp
Route::get('/filter',[FilterController::class, 'index']);
// tìm kiếm theo điều kiện
Route::get('/filter/search',[FilterController::class, 'filter']);
