<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    protected $table = 'attendance_requests';
    
    protected $fillable = [
        'user_id',
        'type',
        'target_date',
        'request_time',
        'reason',
        'status',
        'approver_id',
        'approved_at',
        'admin_comment',
    ];

    protected $casts = [
        'target_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public const TYPE_LABELS = [
        'late' => '遅刻',
        'early_leave' => '早退',
        'absence' => '欠勤',
    ];

    public const STATUS_LABELS = [
        'pending' => '申請中',
        'approved'=> '承認済み',
        'rejected'=> '却下',
    ];

    // この申請をだしたユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}