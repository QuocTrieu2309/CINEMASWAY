<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Role\RoleController;

Route::group(['prefix' => 'dashboard/role'], function () {
    // GET Role Route
    Route::get('/',[RoleController::class,'index']);
    //Create Role Route
    Route::post('/create',[RoleController::class,'store']);
    //  Update Role Route
    Route::put('/update/{id}',[RoleController::class,'update']);
    //  Delete Role Route
    Route::delete('/delete/{id}',[RoleController::class,'destroy']);
});