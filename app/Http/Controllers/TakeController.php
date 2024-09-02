<?php

namespace App\Http\Controllers;

use \Log;
use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Quiz\CreateOptionQuizRequest;
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
use Illuminate\Support\Facades\DB;

class TakeController extends ApiController
{ 
    public function startQuiz(createTakeRequest $request)
    {
        $validated = $request->validated();
        try {
            $userId = auth()->user()->id;
            $quizId = $validated['quiz_id'];
            
            DB::beginTransaction(); 

            $quiz = Quiz::findOrFail($quizId);

                    // برسی اینکه ایا ازمون شروع شده یا خیر
            if ($quiz->status !== 2) {
                return response()->json(['error' => 'آزمون هنوز شروع نشده است یا قبلاً پایان یافته است.'], 403);
            }
    
            $currentTime = Carbon::now();
            if ($currentTime->lt($quiz->started_at)) 
                return response()->json(['error' => 'آزمون هنوز شروع نشده است.'], 403);
                
                if ($currentTime->gt($quiz->finished_at)) 
                    return response()->json(['error' => 'آزمون پایان یافته است.'], 403);
                
                    if (!$quiz) 
                        return response()->json(['error' => 'آزمون یافت نشد.'], 404);
                
    
            $take = Take::create([
                'user_id' => $userId,       
                'quiz_id' => $quizId,
                'started_at' => Carbon::now(),
            ]);

            $this->generateTakeQuestions($take->id);

            DB::commit();

            return $this->respondCreated('ازمون شروع شد', $take);            
        } catch (\Throwable $th) {
            return $this->respondInternalError('شروع ازمون با مشکل روبه رو شد');
        }
    }



        // ذخیره سوالات به صورت رندم و نمایش ان به کاربر
    public function generateTakeQuestions($takeId)
    {
        $take = Take::findOrFail($takeId);
        $quizId = $take->quiz_id;
        $userId = $take->user_id;
            
        $quizConfigs = Quiz_config::where('quiz_id', $quizId)->get();

        $questionIds = [];

        foreach ($quizConfigs as $config) {
                $levelValue = Quiz_question::getLevelValue($config->level);

            $questions = Quiz_question::where('category_id', $config->category_id)
                                    ->where('level', $levelValue)
                                    ->inRandomOrder()
                                    ->take($config->number_question)
                                    ->pluck('id')
                                    ->toArray();

           $questionIds = array_merge($questionIds, $questions);
        }

        $take->questions = json_encode($questionIds);
        $take->started_at = now();
        $take->save();


                // نمایش سوالات ذخیره شده
        $questions = Quiz_question::whereIn('id', $questionIds)
                                    ->simplePaginate(1);

        if ($questions->isEmpty()) {
            return $this->endQuiz($takeId);
        }

        return response()->json($questions);
    }




            // ثبت گزینه های شرکت کننده 
    public function submitAnswer(CreateOptionQuizRequest $request)
    {
        try {
            $takeId = $request->json('take_id');
            $answers = $request->json('answers');
            
            $take = Take::findOrFail($takeId);
    
            $currentAnswers = $take->answers;
    
            if (is_null($currentAnswers) || !is_array($currentAnswers)) {
                $currentAnswers = [];
            }
    
            foreach ($answers as $answer) {
                $questionId = $answer['question_id'];
                $selectedOption = $answer['selected_option'];
    
                $existingAnswerKey = array_search($questionId, array_column($currentAnswers, 'question_id'));
    
                if ($existingAnswerKey !== false) {
                    $currentAnswers[$existingAnswerKey]['selected_option'] = $selectedOption;
                } else {
                    $currentAnswers[] = [
                        'question_id' => $questionId,
                        'selected_option' => $selectedOption,
                    ];
                }
            }
    
            $take->update(['answers' => $currentAnswers]);
    
            return $this->respondCreated('جواب با موفقیت ارسال شد.', $take);
    
        } catch (\Exception $e) {
            return $this->respondInternalError('خطایی در ثبت پاسخ کاربر به وجود آمد', $e);
        }
    }




            // پایان ازمون
    public function endQuiz($takeId)
    {
        $take = Take::find($takeId);

        if (!$take) {
            return response()->json(['error' => 'take یافت نشد.'], 404);
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
            $take->status = Take::STATUS_LATE;
        } else if ($totalScore >= $passPercentage) {
            $take->status = Take::STATUS_PASSED;
        } else {
            $take->status = Take::STATUS_FAILED;
        }
        
        $take->score = $totalScore;
        $take->save();


        $startTime = Carbon::parse($take->started_at);
        $endTime = Carbon::parse($take->finished_at);
        $duration = $endTime->diff($startTime);
    
        return response()->json([
            'message' => 'آزمون به پایان رسید.',
            'status' => Take::getStatusText($take->status),
            'score' => $totalScore,
            'time_taken' => $duration->format('%H:%I:%S')
        ], 200);    

    }




    // محاسبه نمره کاربر 
    private function calculateTotalScore(Take $take)
{
    $totalScore = 0;

    $answers = json_decode($take->answers, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $answers = [];
    }

    $userAnswers = collect($answers)->pluck('selected_option', 'question_id')->toArray();

    $questionIds = json_decode($take->questions, true); 

    $questions = Quiz_question::whereIn('id', $questionIds)->get();

        foreach ($questions as $quizQuestion) {
            $options = $quizQuestion->options;

            $correctOption = collect($options)->firstWhere('is_correct', true);
            if (isset($userAnswers[$quizQuestion->id]) && $userAnswers[$quizQuestion->id] == $correctOption['option_number']) {
                $totalScore += $quizQuestion->score;
            }
        }

    return $totalScore;
} 
}




