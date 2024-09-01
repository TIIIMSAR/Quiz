<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz_question extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];


    protected $casts = [
        'options' => 'array',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    public static function getLevelValue($level)
    {
        $levels = [
            'آسان' => 1,
            'متوسط' => 2,
            'سخت' => 3,
        ];

        return $levels[$level] ?? null;
    }

    public function configs()
    {
        return $this->belongsToMany(Quiz_config::class, 'quiz_config_question', 'question_id', 'quiz_config_id');
    }


}
