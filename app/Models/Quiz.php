<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];


    protected $casts = [
        'start_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    const STATUS_CREATED = 1; 
    const STATUS_STARTED = 2; 
    const STATUS_FINISHED = 3; 
    const STATUS_STOPPED = 4; 


    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case 1:
                return 'آزمون ساخته شده است';
            case 2:
                return 'آزمون شروع شده است';
            case 3:
                return 'آزمون پایان یافته است';
            case 4:
                return 'ازمون متوقف شده است';
            default:
                return 'وضعیت نامشخص';
        }
    }

    protected $dates = ['start_at', 'finished_at'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function configs()
    {
        return $this->hasMany(Quiz_config::class);
    }

    public function takes()
    {
        return $this->hasMany(Take::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Take::class, 'quiz_id', 'id', 'id', 'user_id');
    }

}
