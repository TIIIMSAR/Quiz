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


    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

}
