<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DakokuRequest extends Model
{
    use HasFactory;

    // テーブル名を明示的に指定
    protected $table = 'dakoku_requests';

    protected $fillable = [
        'user_id',
        'date', 
        'is_in_request', 
        'is_out_request',
        'is_delete',
        'requested_punch_in', 
        'requested_punch_out', 
        'reason', 
        'status', 
        'admin_comment'
    ];

    // 申請したユーザーへのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
