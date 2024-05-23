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
//Translation route
require_once __DIR__ . '/translation.php';
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
//Ticket_type route
require_once __DIR__ . '/ticket_type.php';
//Showtime route
require_once __DIR__ . '/showtime.php';
