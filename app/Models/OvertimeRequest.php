<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model 
{
    protected $table = 'overtime_requests';

    protected $fillable = [
        'user_id',
        'target_date',
        'start_at',
        'end_at',
        'reason',
        'status',
        'approver_id',
        'approved_at',
        'admin_comment',
    ];

    protected $casts = [
        'target_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public const STATUS_LABELS = [
        'pending' => '申請中',
        'approved' => '承認済み',
        'rejected' => '却下',
    ];

    // この申請を出したユーザー
    public function user()
    {
        return $this -> belongsTo(User::class);
    }
}