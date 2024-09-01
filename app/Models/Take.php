<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Take extends Model
{
    use HasFactory;


    protected $guarded = [
        'id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function takeQuestions()
    {
        return $this->hasMany(Take_question::class, 'take_id');
    }


    public function questions()
    {
        return $this->hasMany(Take_question::class);
    }

}
