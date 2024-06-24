<?php

use App\Http\Controllers\API\Client\ChooseSeatController;
use App\Http\Controllers\API\Client\ClientController;
use App\Http\Controllers\API\Client\FilterController;
use Illuminate\Support\Facades\Route;
Route::post('booking',[ClientController::class, 'updateTickets']);
// Get city information
Route::get('/filter/movie/{id}',[FilterController::class, 'getCity']);
// tìm kiếm theo năm :tháng:ngày, thành phố , mànhinh:phụđề (show time)
Route::get('/filter/movie/{id}/showtime',[FilterController::class, 'filter']);
// tìm kiếm theo thành phố , năm:tháng:ngày , tên rạp  filter(movie)
Route::get('/filter/movie',[FilterController::class, 'filterMovie']);
// tìm kiếm theo màn hình filterScreen(movie)
Route::get('/filter/screen',[FilterController::class, 'filterScreenMovie']);
// tìm kiếm theo the loai filterGenre(movie)
Route::get('/filter/genre',[FilterController::class, 'filterGenreMovie']);
// tìm kiếm theo phu de filtersubtitle(movie)
Route::get('/filter/subtitle',[FilterController::class, 'filterSubtitleMovie']);
//Post tạo mới booking
Route::post('/create-booking',[ClientController::class, 'createBooking']);
//Post tạo mới booking
Route::post('/create-booking-service',[ClientController::class, 'createBooingService']);
//Post hiển thị danh sách ghế
Route::get('/show-seat-map/{showtime_id}', [ChooseSeatController::class, 'showSeatMap']);
//Post chọn ghế
Route::post('/status', [ChooseSeatController::class, 'updateStatusSeat']);
//Post hủy ghế
Route::post('/cancel', [ChooseSeatController::class, 'cancel']);
