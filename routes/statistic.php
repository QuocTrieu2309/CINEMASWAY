<?php

use App\Http\Controllers\API\Revenue\RevenueController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dashboard/statistic'], function () {
    // thống kê tổng doanh thu
    Route::get('/total-revenue', [RevenueController::class, 'totalRevenue']);
    // thông kê doanh thu theo rạp
    Route::get('/cinema-revenue/{cinema_id}', [RevenueController::class, 'dailyRevenue']);
    // thống kê phim bán được nhiều vé nhất hiện đang chiếu
    Route::get('/cinema-revenue-films', [RevenueController::class, 'revenueFilms']);
});
