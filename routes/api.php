<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizQuestionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TakeController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::fallback(function(){
    return response()->json('ادرس درست وارد نشده است');
});

Route::get('/search', [SearchController::class, 'search'])->middleware('auth:sanctum');


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

    Route::group(['prefix' => '/azmmon'], function () {
        Route::get('/{id}', [QuizController::class, 'index']);
        Route::post('/create', [QuizController::class, 'store']);
        Route::post('/config', [QuizController::class, 'config']);
        Route::get('/show-config/{id}', [QuizController::class, 'showQuizConfig']);


        Route::post('/generate_url', [QuizController::class, 'regenerateQuizUrl']);
        Route::post('/expire_url', [QuizController::class, 'expireQuizUrl']);
    });

    Route::group(['prefix' => '/category'], function () {
        Route::post('', [CategoryController::class, 'store']);
        Route::get('', [CategoryController::class, 'index']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::put('/{id}', [CategoryController::class, 'update']);
    });

    Route::group(['prefix' => '/quiz'], function () {
        Route::get('', [QuizQuestionController::class, 'index']);
        Route::post('', [QuizQuestionController::class, 'makeQuiz']);
        Route::delete('/{id}', [QuizQuestionController::class, 'destroy']);
    });

    Route::group(['prefix' => '/take'], function () {
        Route::post('/start', [TakeController::class, 'startQuiz']);
        Route::post('/finished', [TakeController::class, 'endQuiz']);
        Route::post('/submit-answer', [TakeController::class, 'store']);

        Route::post('', [TakeController::class, 'getQuestions']);
    });
});
