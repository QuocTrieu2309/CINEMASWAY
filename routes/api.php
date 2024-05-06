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
