<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'day_type',
        'reason',
        'status',
        'approver_id',
        'approved_at',
        'admin_comment',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // 種別の日本語ラベル(画面表示用)
    public const TYPE_LABELS = [
        'paid' => '有給',
        'special' => '特別休暇',
    ];

    public const DAY_TYPE_LABELS = [
        'full_day' => '全日',
        'am' => '午前半休',
        'pm' => '午後半休',
    ];

    public const STATUS_LABELS = [
        'pending' => '申請中',
        'approved'=> '承認済み',
        'rejected'=> '却下',
    ];

    // この申請を出したユーザー
    public function user() 
    {
        return $this->belongsTo(User::class);
    }
}