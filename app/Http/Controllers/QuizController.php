<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Azmmon\CreateAzmmonRequest;
use App\Models\Owner;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends ApiController
{
/**
 * Display a listing of the resource.
 */
public function index()
{
    //
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

        $urlQuiz = $this->generateUniqueQuizUrl(); 

        $quiz = Quiz::create([
            'title' => $request->input('title'),
            'summary' => $request->input('summary'),
            'published' => $request->input('published'),
            'score' => $request->input('score'),
            'owner_id' => Auth::id(),
            'url_quiz' => $urlQuiz,
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



private function generateUniqueQuizUrl()
{
    return url('/quiz/' .  uniqid());
}

public function regenerateQuizUrl($quizId)
{
    try {
        $quiz = Quiz::findOrFail($quizId);
        
        if ($quiz->owner_id !== Auth::id()) {
            return response()->json(['error' => 'شما اجازه این کار را ندارید.'], 403);
        }

        $quiz->url_quiz = $this->generateUniqueQuizUrl();
        $quiz->save();

        return response()->json(['message' => 'لینک آزمون با موفقیت بازتولید شد.', 'new_url' => $quiz->url_quiz]);
    } catch (\Throwable $e) {
        return $this->respondInternalError('خطایی در هنگام بازتولید لینک آزمون رخ داد.', $e);
    }
}  


public function expireQuizUrl($url)
{   
    try {
        $quiz = Quiz::where('url_quiz', $url)->firstOrFail();
        
        if ($quiz->owner_id !== Auth::id()) {
            return response()->json(['error' => 'شما اجازه این کار را ندارید.'], 403);
        }

        $quiz->url_quiz = null;
        $quiz->save();

        return response()->json(['message' => 'لینک آزمون با موفقیت منقضی شد.']);
    } catch (\Throwable $e) {
        return $this->respondInternalError('خطایی در هنگام منقضی کردن لینک آزمون رخ داد.', $e);
    }
}
}
