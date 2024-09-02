<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuizConfigController;
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
Route::get('/quiz/{quizId}/{uniqueId}', [QuizController::class, 'showQuizLinK']);


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
        Route::get('/{id}', [QuizController::class, 'show']);
        Route::post('/create', [QuizController::class, 'store']);
        Route::put('/start', [QuizController::class, 'startQuiz']);

        Route::post('/config', [QuizConfigController::class, 'createConfig']);
        Route::get('/show-config/{id}', [QuizConfigController::class, 'showQuizConfig']);

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
        Route::post('/submit-answer', [TakeController::class, 'submitAnswer']);
        Route::post('get-questions/take-id/{takeId}', [TakeController::class, 'generateTakeQuestions']);
    });

    Route::group(['prefix' => '/page'], function () {
        Route::get('show-category', [CategoryController::class, 'index']);
        Route::get('show-all-questions', [QuizQuestionController::class, 'index']);
        Route::get('show-detail-question/{id}', [QuizQuestionController::class, 'show']);
        Route::get('show-all-azmmon', [QuizController::class, 'index']);
        Route::get('show-all-azmmon-user/{idQuiz}', [QuizController::class, 'getQuizUserList']);
        Route::get('show-all-azmmon-public', [UserController::class, 'getPublicAzmmon']);
        Route::get('show-detail-azmmon-public/{idQuiz}', [UserController::class, 'getPublicAzmmonDetail']);
    }); 
});
