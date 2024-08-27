<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::fallback(function(){
    return response()->json('ادرس درست وارد نشده است');
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::delete('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::delete('/delete_user', [AuthController::class, 'deleteUser'])->middleware('auth:sanctum');


// Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
// Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

Route::get('/users', [UserController::class, 'index']);


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => '/users'], function () {
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
    });

    Route::group(['prefix' => '/quiz'], function () {
        Route::post('/create', [QuizController::class, 'store']);
    });

    Route::group(['prefix' => '/category'], function () {
        Route::post('', [CategoryController::class, 'store']);
        Route::get('', [CategoryController::class, 'index']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::put('/{id}', [CategoryController::class, 'update']);
    });
});
