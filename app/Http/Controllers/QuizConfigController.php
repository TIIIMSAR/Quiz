<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Azmmon\AzmmonConfigRequest;
use App\Models\Quiz;
use App\Models\Quiz_config;
use Illuminate\Http\Request;

class QuizConfigController extends ApiController
{
    
public function createConfig(AzmmonConfigRequest $request)
{
    $validator = $request->validated();

    // try {
        $quizId = $request->input('quiz_id');
        $categoryId = $request->input('category_id');
        $numberQuestions = $request->input('number_question');
        $level = $request->input('level');

        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            return response()->json(['error' => 'آزمون مورد نظر یافت نشد.'], 404);
        }

        $quizConfig = Quiz_config::updateOrCreate(
            ['quiz_id' => $quizId],
            [
                'category_id' => $categoryId,
                'number_question' => $numberQuestions,
                'level' => $level,
            ]
        );

        return $this->respondSuccess('تنظیمات با موفقیت اعمال شد.', $quizConfig);

    // } catch (\Exception $e) {
    //     return $this->respondInternalError('تنظیمات شما با خطا رو به شد');
    // }
}





public function showQuizConfig($quizId)
{
    $quiz = Quiz::find($quizId);
    // dd($quiz);
    if (!$quiz) {
        return response()->json(['error' => 'آزمون مورد نظر یافت نشد.'], 404);
    }

    $quizConfig = Quiz_config::where('quiz_id', $quizId)->first();

    if (!$quizConfig) {
        return response()->json(['error' => 'تنظیمات برای آزمون مورد نظر یافت نشد.'], 404);
    }

    $userId = auth()->user()->id;
    
    $isOwner = ($quiz->owner_id === $userId);
    
    if (!$isOwner) {
        return response()->json(['error' => 'دسترسی غیرمجاز'], 403);
    }

        return $this->respondSuccess('کانفیگ ها با موفقیت پیدا شدند.', $quizConfig);
}

}
