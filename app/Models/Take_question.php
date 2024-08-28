<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Take_question extends Model
{
    use HasFactory;

    
    protected $guarded = [
        'id'
    ];

    public function take()
    {
        return $this->belongsTo(Take::class, 'take_id');
    }

    public function question()
    {
        return $this->belongsTo(Quiz_question::class, 'question_id');
    }

    public function takeAnswers()
    {
        return $this->hasMany(Take_answer::class, 'take_question_id');
    }
}
