<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Take_answer extends Model
{
    use HasFactory;


    protected $guarded = [
        'id'
    ];


    public function take()
    {
        return $this->belongsTo(Take::class, 'take_id');
    }

    public function takeQuestion()
    {
        return $this->belongsTo(Take_question::class, 'take_question_id');
    }

    public function option()
    {
        return $this->belongsTo(Option::class, 'option_id');
    }
}
