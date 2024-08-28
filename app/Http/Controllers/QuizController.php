<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Azmmon\CreateAzmmonRequest;
use App\Http\Requests\Azmmon\expireUrlAzmmonRequest;
use App\Http\Requests\Azmmon\GenerateUrlAzmmonRequest;
use App\Models\Owner;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends ApiController
{
/**
 * Display a listing of the resource.
 */
public function index($quizId)
{
    try {
        $userId = Auth::id();

        $quizzes = Quiz::where('id', $quizId)->where('owner_id', $userId)->firstOrFail();

        return $this->respondCreated('نمایش ازمون با موفقیت انجام شد.', $quizzes);  
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'آزمون مورد نظر پیدا نشد یا شما به آن دسترسی ندارید.'], 403);
    } catch (\Throwable $e) {
        return response()->json(['error' => 'خطایی در بازیابی آزمون‌ها رخ داد.'], 500);
    }
}

/**
 * Store a newly created resource in storage.
 */
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

/**
 * Display the specified resource.
 */
public function show(string $id)
{
    //
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, string $id)
{
    //
}

/**
 * Remove the specified resource from storage.
 */
public function destroy(string $id)
{
    //
}



private function generateUniqueQuizUrl($quizId)
{
        return url('/quiz/' . $quizId . '/' . uniqid());
}


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

        return response()->json(['message' => 'لینک آزمون با موفقیت بازتولید شد.', 'new_url' => $quiz->url_quiz]);
    } catch (\Throwable $e) {
        return $this->respondInternalError('خطایی در هنگام بازتولید لینک آزمون رخ داد.', $e);
    }
}


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
