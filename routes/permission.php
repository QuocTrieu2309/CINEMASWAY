<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Permission\PermissionController;

Route::group(['prefix' => 'dashboard/permission'], function () {
    // GET Permission Route
    Route::get('/',[PermissionController::class,'index']);
    // //Create Permission Route
    // Route::post('/create',[PermissionController::class,'store']);
    // //  Update Permission Route
    // Route::put('/update/{id}',[PermissionController::class,'update']);
    // //  Delete Permission Route
    // Route::delete('/delete/{id}',[PermissionController::class,'destroy']);
});