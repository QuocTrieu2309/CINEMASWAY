<?php
use App\Http\Controllers\API\Client\ClientController;
use App\Http\Controllers\API\Client\FilterController;
use Illuminate\Support\Facades\Route;

Route::post('booking',[ClientController::class, 'updateTickets']);
// tìm kiếm theo năm :tháng:ngày, thành phố , mànhinh:phụđề (show time)
Route::get('/filter/showtime',[FilterController::class, 'filter']);
// tìm kiếm theo thành phố , năm:tháng:ngày , tên rạp  filter(movie)
Route::get('/filter/movie',[FilterController::class, 'filterMovie']);
// tìm kiếm theo màn hình filterScreen(movie)
Route::get('/filter/screen',[FilterController::class, 'filterScreenMovie']);
// tìm kiếm theo the loai filterGenre(movie)
Route::get('/filter/genre',[FilterController::class, 'filterGenreMovie']);
// tìm kiếm theo phu de filtersubtitle(movie)
Route::get('/filter/subtitle',[FilterController::class, 'filterSubtitleMovie']);




