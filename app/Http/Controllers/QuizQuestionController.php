<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Quiz\CreateOptionQuizRequest;
use App\Http\Requests\Quiz\CreateQuizRequest;
use App\Models\Category;
use App\Models\Quiz_question;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizQuestionController extends ApiController
{
    public function index () 
    {

    }


public function makeQuiz (CreateOptionQuizRequest $request)
{
    try {
        $category = Category::where('id', $request->category_id)
                            ->where('owner_id', auth()->id())
                            ->first();

        if (!$category) {
            return $this->respondNotFound('دسته‌بندی یافت نشد یا به شما تعلق ندارد.');
        }

        $validatedData = $request->validated();

        $question = Quiz_question::create([
            'category_id' => $validatedData['category_id'],
            'content' => $validatedData['content'],
            'level' => $validatedData['level'],
            'score' => $validatedData['score'],
            'options' => $validatedData['options'], 
        ]);

        return response()->json(['message' => 'سوال با موفقیت ثبت شد.', 'question' => $question], 201);

    } catch (ModelNotFoundException $e) {
        return $this->respondNotFound('دسته‌بندی یافت نشد.');
    } catch (\Exception $e) {
        return $this->respondInternalError('خطا در ایجاد سوال. لطفاً دوباره تلاش کنید.');
    }
}


public function destroy($id)
{
    try {
        $question = Quiz_question::findOrFail($id);
        
        $category = Category::findOrFail($question->category_id);

        if ($category->owner_id !== Auth::id()) {
            return response()->json(['error' => 'شما اجازه حذف این سوال را ندارید.'], 403);
        }

        $question->delete();

        return response()->json(['message' => 'سوال با موفقیت حذف شد.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'خطایی در هنگام حذف سوال رخ داد.'], 500);
    }
}



}

