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



    const STATUS_LATE = 1;
    const STATUS_PASSED = 2;
    const STATUS_FAILED = 3;

    public static function getStatusText($status)
    {
        $statuses = [
            self::STATUS_LATE => 'شما دیر کردید ازمون به پایان رسیده است',
            self::STATUS_PASSED => 'شما نمره قبولی را کسب کردید',
            self::STATUS_FAILED => 'شما مردود شدید',
        ];

        return $statuses[$status] ?? 'وضعیت ثبت نشده است';
    }



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
