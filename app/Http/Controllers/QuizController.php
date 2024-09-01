<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Azmmon\AzmmonConfigRequest;
use App\Http\Requests\Azmmon\CreateAzmmonRequest;
use App\Http\Requests\Azmmon\expireUrlAzmmonRequest;
use App\Http\Requests\Azmmon\GenerateUrlAzmmonRequest;
use App\Http\Requests\Azmmon\startAzmmonRequest;
use App\Http\Resources\AzmmonDetailResource;
use App\Http\Resources\AzmmonResource;
use App\Http\Resources\UserResource;
use App\Models\Owner;
use App\Models\Quiz;
use App\Models\Quiz_config;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends ApiController
{
/**
 * Display a listing of the resource.
 */    
        // نمایش همه ازمون ها به صورت خلاصه
    public function index()
    {
        try {
            $userId = Auth::id();

            $quizzes = Quiz::where('owner_id', $userId)->get();

            return $this->respondCreated('نمایش آزمون‌ها با موفقیت انجام شد.', AzmmonResource::collection($quizzes));  

        } catch (\Throwable $e) {
            return response()->json(['error' => 'خطایی در بازیابی آزمون‌ها رخ داد.'], 500);
        }
    }


        // نمایش ازمون با تمام اطلاعات
    public function show($quizId)
    {
        try {
            $userId = Auth::id();

            $quizzes = Quiz::where('id', $quizId)
                                ->where('owner_id', $userId)
                                ->with('configs')
                                ->firstOrFail();

            return $this->respondCreated('نمایش ازمون با موفقیت انجام شد.', new AzmmonDetailResource($quizzes));  
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'آزمون مورد نظر پیدا نشد یا شما به آن دسترسی ندارید.'], 403);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'خطایی در بازیابی آزمون‌ها رخ داد.'], 500);
        }
    }

        // کاربران شرکت کننده در ازمون
    public function getQuizUserList(Request $request, $quizId)
    {
        $paginate = $request->input('paginate', 10); 

        try {
            $quiz = Quiz::findOrFail($quizId);
    
            $users = $quiz->users()->simplePaginate($paginate);
    
            return $this->respondCreated('لیست کاربران شرکت‌کننده با موفقیت بازیابی شد.', UserResource::collection($users)->response()->getData(true));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'آزمون مورد نظر پیدا نشد.'], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'خطایی در بازیابی لیست کاربران رخ داد.'], 500);
        }
    }

            // ساخت ازمون 
    public function store(CreateAzmmonRequest $request)
    {
        try {   
            if (!Auth::check()) {
                return response()->json(['error' => 'ابتدا باید وارد حساب کاربری خود شوید.'], 401);
            }
            $ownerExists = Owner::where('user_id', Auth::id())->exists();

            if (!$ownerExists) {
                Owner::create([
                    'user_id' => Auth::id(),
                ]);
            }

            $quiz = Quiz::create([
                'title' => $request->input('title'),
                'summary' => $request->input('summary'),
                'published' => $request->input('published'),
                'score' => $request->input('score'),
                'owner_id' => Auth::id(),
            ]);

        
            return $this->respondCreated('ازمون با موفقیت ساخته شد.', $quiz);

        } catch (\Throwable $e) {
            return $this->respondInternalError('خطایی در هنگام ایجاد آزمون رخ داد. لطفا دوباره تلاش کنید.',$e);
            
        }
    }



            //شروع ازمون از زمان فعلی
    public function startQuiz(startAzmmonRequest $request)
    {
        $quizId = $request->input('quiz_id');
        try {
            $quiz = Quiz::where('id', $quizId)->where('owner_id', Auth::id())->firstOrFail();
    
            if ($quiz->started_at !== null) {
                return response()->json(['error' => 'این آزمون قبلاً شروع شده است.'], 400);
            }
    
            $startAt = Carbon::now();
    
            $durationMinutes = $request->input('duration_minutes');
            
            if (!$durationMinutes || !is_numeric($durationMinutes)) {
                return response()->json(['error' => 'لطفاً مدت زمان معتبر برای آزمون وارد کنید.'], 400);
            }
    
            $finishedAt = $startAt->copy()->addMinutes($durationMinutes);
    
            $quiz->update([
                'started_at' => $startAt,
                'finished_at' => $finishedAt,
                'status' => Quiz::STATUS_STARTED,
            ]);
    
            return response()->json([
                'message' => 'آزمون با موفقیت شروع شد.',
                'started_at' => $startAt->toDateTimeString(),
                'finished_at' => $finishedAt->toDateTimeString()
            ], 200);
    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'آزمون مورد نظر پیدا نشد یا شما مجاز به شروع آن نیستید.'], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'خطایی در شروع آزمون رخ داد.'], 500);
        }
    }

            // لینک ازمون
    private function generateUniqueQuizUrl($quizId)
    {
            return url('/quiz/' . $quizId . '/' . uniqid());
    }


                // چک کردن اینکه ایا زمان ازمون تموم شده یا ن
        public function checkAndFinishQuizzes()
    {
        $now = Carbon::now();

        $quizzes = Quiz::where('status', Quiz::STATUS_STARTED)
                        ->where('finished_at', '<=', $now)
                        ->get();

        foreach ($quizzes as $quiz) {
            $quiz->update(['status' => Quiz::STATUS_FINISHED]);
        }
    }



                // ساخت لینک ازمون
    public function regenerateQuizUrl(GenerateUrlAzmmonRequest $request)
    {
        try {
            $quizId = $request->input('quiz_id'); 
            
            $quiz = Quiz::findOrFail($quizId);
            
            if ($quiz->owner_id !== Auth::id()) {
                return response()->json(['error' => 'شما اجازه این کار را ندارید.'], 403);
            }

            $quiz->url_quiz = $this->generateUniqueQuizUrl($quizId);
            $quiz->save();

            return response()->json(['message' => 'لینک آزمون با موفقیت تولید شد.', 'new_url' => $quiz->url_quiz]);
        } catch (\Throwable $e) {
            return $this->respondInternalError(' خطایی در هنگام بازتولید لینک آزمون رخ داد مطمعن شوید که مقادیر را به دسترسی ارسال کردید', $e);
        }
    }



                // منقضی کردن لینک ازمون
    public function expireQuizUrl(expireUrlAzmmonRequest $request)
    {   
        try {
            $url = $request->input('url');
            
            $quiz = Quiz::where('url_quiz', $url)->firstOrFail();
            
            if ($quiz->owner_id !== Auth::id()) {
                return response()->json(['error' => 'شما اجازه این کار را ندارید.'], 403);
            }

            $quiz->url_quiz = null;
            $quiz->save();

            return response()->json(['message' => 'لینک آزمون با موفقیت منقضی شد.']);
        } catch (\Throwable $e) {
            return $this->respondInternalError('{مطمعن شوید شناسه صحیح است}:خطایی در هنگام منقضی کردن لینک آزمون رخ داد.', $e);
        }
    }
}
