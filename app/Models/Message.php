<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'user_name',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}