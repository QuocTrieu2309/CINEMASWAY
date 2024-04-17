<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Permission\PermissionController;

Route::group(['prefix' => 'dashboard/permission'], function () {
    // GET Role Route
    Route::get('/',[PermissionController::class,'index']);
    //Create Role Route
    Route::post('/create',[PermissionController::class,'store']);
    //  Update Role Route
    Route::put('/update/{id}',[PermissionController::class,'update']);
    //  Delete Role Route
    Route::delete('/delete/{id}',[PermissionController::class,'destroy']);
});