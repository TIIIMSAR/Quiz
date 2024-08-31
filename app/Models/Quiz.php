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
