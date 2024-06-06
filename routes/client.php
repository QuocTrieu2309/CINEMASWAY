<?php
use App\Http\Controllers\API\Client\ClientController;
use App\Http\Controllers\API\Client\FilterController;
use Illuminate\Support\Facades\Route;

Route::post('booking',[ClientController::class, 'updateTickets']);
// tìm kiếm theo điều kiện filter (show time)
Route::get('/filter/showtime',[FilterController::class, 'filter']);
// tìm kiếm theo điều kiện filter(movie)
Route::get('/filter/movie',[FilterController::class, 'filterMovie']);
