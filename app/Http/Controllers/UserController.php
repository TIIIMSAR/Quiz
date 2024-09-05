<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\QuizDetailResource;
use App\Http\Resources\QuizResource;
use App\Http\Resources\UserQuizResource;
use App\Models\Quiz;
use App\Models\Take;
use App\Models\Quiz_question;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $paginate = $request->input('paginate') ?? 10;
            $sortColumn = $request->input('sort', 'id');
            $sortDirection = Str::startsWith($sortColumn, '-') ? 'desc' : 'asc';
            $sortColumn = ltrim($sortColumn, '-');
    
            $users = User::orderBy($sortColumn, $sortDirection)->simplePaginate($paginate);
            return $this->respondSuccess('لیست کاربران با موفقیت دریافت شد', $users);
        } catch (\Exception $e) {
            return $this->respondInternalError('خطایی در دریافت لیست کاربران رخ داده است');
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return $this->respondSuccess('کاربر با موفقیت پیدا شد', $user);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound('کاربر مورد نظر یافت نشد');
        } catch (\Exception $e) {
            return $this->respondInternalError('خطایی در نمایش اطلاعات کاربر رخ داده است');
        }
    }

    public function update(UpdateUserRequest $request, $id)
    {        
        try {
            $validated = $request->validated();
    
            if (auth()->user()->id != $id) {
                return $this->respondInternalError('شما مجاز به به‌روزرسانی این کاربر نیستید');
            }
    
            $user = User::findOrFail($id);
    
            if (User::where('email', $validated['email'])->where('id', '!=', $id)->exists()) {
                return $this->respondInternalError('ایمیل وارد شده قبلاً استفاده شده است');
            }
    
            $user->update($validated);
    
            return $this->respondSuccess('کاربر با موفقیت به‌روزرسانی شد', $user);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound('کاربر مورد نظر برای به‌روزرسانی یافت نشد');
        } catch (\Exception $e) {
            return $this->respondInternalError('خطایی در به‌روزرسانی کاربر رخ داده است');
        }
    }



    public function getPublicAzmmon(Request $request)
    {
        $paginate = $request->input('paginate', 15); 

        try {
            $publicQuizzes = Quiz::where('published', true)
                                ->simplePaginate($paginate);

            return $this->respondCreated('آزمون‌های عمومی با موفقیت بازیابی شد.', QuizResource::collection($publicQuizzes)->response()->getData(true));
        } catch (\Throwable $e) {
            return response()->json(['error' => 'خطایی در بازیابی آزمون‌های عمومی رخ داد.'], 500);
        }
    }

    public function getPublicAzmmonDetail($quizId)
    {
        try {
            $publicQuiz = Quiz::with('configs')
                            ->where('published', true)
                            ->where('id', $quizId)
                            ->firstOrFail();
    
            return $this->respondCreated('آزمون‌ با موفقیت بازیابی شد.', new QuizDetailResource($publicQuiz));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'آزمون مورد نظر پیدا نشد یا عمومی نشده است.'], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'خطایی در بازیابی آزمون رخ داد.'], 500);
        }
   }



    public function getUserQuizSummary()
    {
        $userId = auth()->user()->id;

        $takes = Take::where('user_id', $userId)
                    ->with('quiz')
                    ->get();

        $summary = $takes->map(function($take) {
            return [
                'id' => $take->id,
                'quiz-id' => $take->quiz->id,
                'quiz-title' => $take->quiz->title ?? 'عنوان ثبت نشده',
                'score' => $take->score,
                'status' => Take::getStatusText($take->status),
            ];
        });

        return response()->json($summary);
    }




    public function getQuizDetails($quizId)
    {
        $userId = auth()->user()->id;
    
        $quiz = Quiz::findOrFail($quizId);
    
        $take = Take::where('user_id', $userId)
                    ->where('quiz_id', $quizId)
                    ->firstOrFail();
    
        $questions = json_decode($take->questions, true);
        $answers = json_decode($take->answers, true);
    
        $answeredQuestions = Quiz_question::whereIn('id', $questions)->get();
    
        $result = [
            'owner' => [
                'owner_name' => $quiz->owner->name,
                'owner_email' => $quiz->owner->email
            ],
            'quiz_title' => $quiz->title,
            'quiz_summary' => $quiz->summary,
            'published' => $quiz->published ? 'ازمون عمومی' : 'ازمون خصوصی',
            'status_time' => $quiz->status_text,
            'passing_azmmon_score' => $quiz->score,
            'user_score' => $take->score,
            'status' => Take::getStatusText($take->status),
            'questions' => []
        ];
    
        foreach ($answeredQuestions as $question) {
            // پیدا کردن پاسخ کاربر برای سوال فعلی
            $userAnswer = null;
            foreach ($answers as $answer) {
                if ($answer['question_id'] == $question->id) {
                    $userAnswer = $answer['selected_option'];
                    break;
                }
            }
    
            $result['questions'][] = [
                'id' => $question->id,
                'question_content' => $question->content,
                'question_category_name' => $question->category->name,
                'question_level' => $question->level,
                'question_scoer' => $question->score,
                'user_answer' => $userAnswer !== null ? $userAnswer : 'پاسخ داده نشده',
                'options' => $question->options
            ];
        }
    
        return response()->json($result, 200);
    }

}
