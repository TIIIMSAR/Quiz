<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correct_answers extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];



    public function question()
    {
        return $this->belongsTo(Quiz_question::class, 'question_id');
    }

    public function correctOption()
    {
        return $this->belongsTo(Option::class, 'correct_option_id');
    }
}
