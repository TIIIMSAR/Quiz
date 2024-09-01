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
        // dd('test');
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



    //نمایش سوالات ازمون
    public function generateTakeQuestions($takeId)
    {
        $take = Take::findOrFail($takeId);
        $quizId = $take->quiz_id;
        $userId = $take->user_id;
            // dd($take);
        $quizConfigs = Quiz_config::where('quiz_id', $quizId)->get();
// dd($quizConfigs);
        $questionIds = [];

        foreach ($quizConfigs as $config) {
            $questions = Quiz_question::where('category_id', $config->category_id)
                                    // ->where('level', $config->level)
                                    ->inRandomOrder()
                                    ->take($config->number_question)
                                    ->get();

            foreach ($questions as $question) {
                $questionIds[] = $question->id;
            }
        }

        Take_question::create([
            'user_id' => $userId,
            'take_id' => $takeId,
            'questions' => json_encode($questionIds), // تبدیل آرایه به JSON و ذخیره آن
        ]);

        return $this->respondSuccess('سوالات آزمون با موفقیت ایجاد و ذخیره شدند.', $questionIds);
    }

    
            

    //رندم سازی و سوالات بعدی
    function getNextQuestionForUser($takeId, $userId)
    {
        $unansweredQuestions = Take_question::where('take_id', $takeId)
                                            ->where('user_id', $userId)
                                            ->whereDoesntHave('takeAnswer')
                                            ->inRandomOrder()
                                            ->get();
    
        if ($unansweredQuestions->isNotEmpty()) {
            $nextQuestion = $unansweredQuestions->first();
            return $nextQuestion->question;
        }
    
        return response()->json(['message' => 'سوال دیگری برای این آزمون باقی نمانده است.'], 200);
    }




public function submitAnswer(CreateOptionQuizRequest $request)
{
    $request->validated();
    
    try {
        $takeId = $request->input('take_id');
        $questionId = $request->input('question_id');
        $takeQuestionId = $request->input('take_question_id');
        $selectedOption = $request->input('selected_option');

            //find or create a new    
        $takeAnswer = Take_answer::firstOrCreate(
            ['take_id' => $takeId],
            ['take_question_id' => $takeQuestionId],
            ['answers' => json_encode([])]
        );
        
        $currentAnswers = $takeAnswer->answers;

            // json_decode => array
        if (is_string($currentAnswers)) {
            $currentAnswers = json_decode($currentAnswers, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                $currentAnswers = [];
            }
        }
                //add new question
        $currentAnswers[] = [
            'question_id' => $questionId,
            'selected_option' => $selectedOption,
        ];

        $takeAnswer->update(['answers' => $currentAnswers]);

        return $this->respondCreated('جواب ابا موفقیت ارسال شد.', $takeAnswer);

    } catch (\Exception $e) {
        return $this->respondInternalError('خطایی در ثبت پاسخ کاربر به وجود امد', $e);
    }
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





    // score calculation
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




