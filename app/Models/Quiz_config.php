<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz_config extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    public function getLevelAttribute($value)
    {
        $levels = [
            1 => 'آسان',
            2 => 'متوسط',
            3 => 'سخت',
        ];

        return $levels[$value] ?? 'نامشخص';
    }


    public function questions()
    {
        return $this->hasMany(Quiz_question::class, 'category_id', 'category_id')
                    ->where('level', $this->level);
    }
}
