<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiz\createQuizRequest;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function store (createQuizRequest $requst)
    {
        dd('test');
    }

    // $quiz->start_at = now();
    // $quiz->finished_at = now()->addHours(2);
}
