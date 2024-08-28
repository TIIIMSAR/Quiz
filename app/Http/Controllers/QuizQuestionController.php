<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Http\Requests\Quiz\CreateQuizRequest;
use App\Models\Category;
use App\Models\Quiz_question;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class QuizQuestionController extends ApiController
{
    public function makeQuiz (CreateQuizRequest $request)
    {
        // try {
            $category = Category::where('id', $request->category_id)
                                ->where('owner_id', auth()->id())
                                ->first();

            if (!$category) {
                return $this->respondNotFound('دسته‌بندی یافت نشد یا به شما تعلق ندارد.');
            }

            $quizQuestion = Quiz_question::create($request->validated());

            return $this->respondCreated('سوال با موفقیت ایجاد شد.', $quizQuestion);

        // } catch (ModelNotFoundException $e) {
        //     return $this->respondNotFound('دسته‌بندی یافت نشد.');
        // } catch (\Exception $e) {
        //     return $this->respondInternalError('خطا در ایجاد سوال. لطفاً دوباره تلاش کنید.');
        // }
    }
}

