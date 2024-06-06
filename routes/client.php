<?php
use App\Http\Controllers\API\Client\ClientController;
use App\Http\Controllers\API\Client\FilterController;
use App\Http\Controllers\API\Client\FilterMovieController;
use Illuminate\Support\Facades\Route;

Route::post('booking',[ClientController::class, 'updateTickets']);
// lấy tất cả thông tin xuất chiếu kèm rạp
Route::get('/filter',[FilterController::class, 'index']);
// tìm kiếm theo điều kiện filter (show time)
Route::get('/filter/showtime',[FilterController::class, 'filter']);
// tìm kiếm theo điều kiện filter(movie)
Route::get('/filter/movie',[FilterMovieController::class, 'filterMovie']);

