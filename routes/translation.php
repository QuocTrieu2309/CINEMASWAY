<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Translation\TranslationController;

Route::group(['prefix' => 'dashboard/translation'], function () {
    // GET Translation Route
    Route::get('/',[TranslationController::class,'index']);
    // GET Show Translation Route
    Route::get('/{id}',[TranslationController::class,'show']);
    //Create Translation Route
    Route::post('/create',[TranslationController::class,'store']);
    //  Update Translation Route
    Route::put('/update/{id}',[TranslationController::class,'update']);
    //  Delete Translation Route
    Route::delete('/delete/{id}',[TranslationController::class,'destroy']);
});