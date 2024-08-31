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

    const LEVEL_EASY = 1;
    const LEVEL_MEDIUM = 2;
    const LEVEL_HARD = 3;

    public function getLevelNameAttribute()
    {
        switch ($this->level) {
            case self::LEVEL_EASY:
                return 'آسان';
            case self::LEVEL_MEDIUM:
                return 'متوسط';
            case self::LEVEL_HARD:
                return 'سخت';
            default:
                return 'نامشخص';
        }
    }

    public function setLevelAttribute($value)
    {
        $this->attributes['level'] = in_array($value, [self::LEVEL_EASY, self::LEVEL_MEDIUM, self::LEVEL_HARD]) ? $value : self::LEVEL_EASY;
    }
}
