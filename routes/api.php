<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Auth route
require_once __DIR__ . '/auth.php';
//Role route
require_once __DIR__ . '/role.php';
//Permission route
require_once __DIR__ . '/permission.php';
//User-Permission route
require_once __DIR__ . '/user_permission.php';
//Screen route
require_once __DIR__ . '/screen.php';
//Cinema_screen route
require_once __DIR__ . '/cinema_screen.php';
//Cinema route
require_once __DIR__ . '/cinema.php';
//Movie route
require_once __DIR__ . '/movie.php';
//Seat route
require_once __DIR__ . '/seat.php';
//Seat-type route
require_once __DIR__ . '/seat_type.php';
//Ticket route
require_once __DIR__ . '/ticket.php';
//Showtime route
require_once __DIR__ . '/showtime.php';
//Showtime route
require_once __DIR__ . '/seat_map.php';
//Booking route
require_once __DIR__ . '/booking.php';
//Service route
require_once __DIR__ . '/service.php';
//Client route
require_once __DIR__ . '/client.php';
//MoMO route
require_once __DIR__ . '/momo.php';
//VN PAy route
require_once __DIR__ . '/vnpay.php';
//Transaction route
require_once __DIR__ . '/transaction.php';





