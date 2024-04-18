<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserPermission\UserPermissionController;

Route::group(['prefix' => 'dashboard/user-permission'], function () {
    // GET User-permission Route
    Route::get('/',[UserPermissionController::class,'index']);
    //Create User-permission Route
    Route::post('/create',[UserPermissionController::class,'store']);
    // //  Update User-permission Route
    // Route::put('/update/{id}',[PermissionController::class,'update']);
    // //  Delete User-permission Route
    // Route::delete('/delete/{id}',[PermissionController::class,'destroy']);
});