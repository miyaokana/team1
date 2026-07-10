<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
    ];

    // この会社に属するユーザー
    public function users()
    {
        return $this->hasMany(User::class);
    }
}