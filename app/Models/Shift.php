<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    // 一括でのデータ挿入・更新を許可するカラムを指定
    protected $fillable = [
        'user_id',
        'shift_date', 
        'start_time',
        'end_time',
    ];

    // 日時カラムを自動的にCarbonインスタンスに変換する設定
    protected $casts = [
        'shift_date' => 'date',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];
}
