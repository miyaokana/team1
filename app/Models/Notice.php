<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'date',
        'is_read',
    ];

    protected $casts = [
        'date' => 'date',
        'is_read' => 'boolean',
    ];
}