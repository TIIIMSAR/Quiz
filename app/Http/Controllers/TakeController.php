<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Taken\createTakeRequest;
use App\Http\Requests\Taken\endTakeRequest;
use App\Http\Requests\Taken\SubmitAnswerRequest;
use App\Models\Correct_answers;
use App\Models\Quiz;
use App\Models\Quiz_config;
use App\Models\Quiz_question;
use App\Models\Take;
use App\Models\Take_answer;
use App\Models\Take_question;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TakeController extends ApiController
{
    public function startQuiz(createTakeRequest $request)
    {
        try {
            $validated = $request->validated();

            $userId = auth()->user()->id;
            $quizId = $validated['quiz_id'];
    
            $quiz = Quiz::find($quizId);
            if (!$quiz) {
                return response()->json(['error' => 'آزمون یافت نشد.'], 404);
            }
    
            $take = Take::create([
                'user_id' => $userId,
                'quiz_id' => $quizId,
                'started_at' => Carbon::now(),
            ]);

            return $this->respondCreated('ازمون شروع شد', $take);            
        } catch (\Throwable $th) {
            return $this->respondInternalError('شروع ازمون با مشکل روبه رو شد');
        }
    }




    public function getQuestions(createTakeRequest $request)
    {
        $validated = $request->validated();

        $quizId = $validated['quiz_id'];

        $questions = Quiz_question::where('quiz_id', $quizId)
            ->inRandomOrder()
            ->get();

        return response()->json(['questions' => $questions], 200);
    }

    

    public function submitAnswer(SubmitAnswerRequest $request)
    {
        $validated = $request->validated();

        $takeId = $validated['take_id'];
        $questionId = $validated['question_id'];
        $answers = $validated['answers'];

        $takeQuestion = Take_question::updateOrCreate([
            'user_id' => $request->user()->id,
            'question_id' => $questionId,
            'take_id' => $takeId,
        ]);

        foreach ($answers as $answer) {
            Take_answer ::updateOrCreate([
                'take_id' => $takeId,
                'take_question_id' => $takeQuestion->id,
                'option_id' => $answer,
            ]);
        }

        return response()->json(['message' => 'پاسخ‌ها با موفقیت ذخیره شد.'], 200);
    }




    public function endQuiz(endTakeRequest $request)
    {
        $validated = $request->validated();

        $takeId = $validated['take_id'];
        $take = Take::find($takeId);

        if (!$take) {
            return response()->json(['error' => 'آزمون یافت نشد.'], 404);
        }

        $quiz = Quiz::find($take->quiz_id);
        if (!$quiz) {
            return response()->json(['error' => 'آزمون یافت نشد.'], 404);
        }

        $take->finished_at = Carbon::now();
        $take->save();

        $totalScore = $this->calculateTotalScore($take);
        $passPercentage = $quiz->score;

        if (Carbon::now()->greaterThan($quiz->finished_at)) {
            $take->status = 'late';
        } else if ($totalScore >= $passPercentage) {
            $take->status = 'passed';
        } else {
            $take->status = 'failed';
        }

        $take->user_score = $totalScore;
        $take->save();

        return response()->json(['message' => 'آزمون به پایان رسید.', 'status' => $take->status, 'score' => $totalScore], 200);
    }


    private function calculateTotalScore(Take $take)
    {
        $totalScore = 0;

        $questions = Take_question::where('take_id', $take->id)->get();
        foreach ($questions as $takeQuestion) {
            $quizQuestion = Quiz_question::find($takeQuestion->question_id);

            if ($quizQuestion) {
                $correctAnswers = Correct_answers::where('question_id', $takeQuestion->question_id)
                                                ->pluck('correct_option_id')
                                                ->toArray();

                $userAnswers = Take_answer::where('take_question_id', $takeQuestion->id)
                                         ->pluck('option_id')
                                         ->toArray();

                if (array_intersect($correctAnswers, $userAnswers) == $correctAnswers) {
                    $totalScore += $quizQuestion->score;
                }
            }
        }

        return $totalScore;
    }

}




