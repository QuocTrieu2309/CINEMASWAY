<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Role\RoleController;

Route::group(['prefix' => 'dashboard/role'], function () {
    // GET Role Route
    Route::get('/',[RoleController::class,'index']);
    //Create Role Route
    Route::post('/create',[RoleController::class,'store']);
});