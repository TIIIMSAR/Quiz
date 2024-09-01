<?php

use App\Http\Controllers\QuizController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;



// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();


Schedule::call(function () {
    app(QuizController::class)->checkAndFinishQuizzes();
})->everyMinute();