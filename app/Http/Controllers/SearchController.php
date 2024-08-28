<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Contract\ApiController;
use App\Models\Quiz;
use Illuminate\Http\Request;

class SearchController extends ApiController
{
    public function search(Request $request)
    {  
    try {
        $searchTerm = $request->query('search', '');

        $quizzes = Quiz::query()
            ->when(!empty($searchTerm), function($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('summary', 'LIKE', "%{$searchTerm}%");
            })
            ->where('published', 1)
            ->simplePaginate(10);

        $quizzes->appends($request->query());

        return $this->respondSuccess('یا موفقیت دریافت شد', $quizzes);
    } catch (\Exception $e) {
        return $this->respondInternalError('با مشکل رو به رو شدید');
    }
    }
}
