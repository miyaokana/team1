<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftCorrection extends Model
{
    protected $table = 'shift_corrections'; // ★ shifts_corrections → shift_corrections に修正

    protected $fillable = [
        'attendance_id',
        'before_check_in',
        'before_check_out',
        'after_check_in',
        'after_check_out',
        'reason',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}