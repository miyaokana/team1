<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\ShiftCorrection;
use Illuminate\Support\Facades\Auth;

class ShiftCorrectionController extends Controller
{

    //デバッグ
    private $testUserId = 1;

    // 修正画面表示
    public function edit()
    {
        //デバッグ
        Auth::loginUsingId($this->testUserId);

        $attendance = Attendance::where('user_id', Auth::id())
            ->latest()
            ->first();

        if (!$attendance) {
            return '勤怠データがありません';
        }

        return view('correction.edit', compact('attendance'));
    }

    // 修正申請処理
    public function store(Request $request)
    {
        //デバッグ
        Auth::loginUsingId($this->testUserId);

        $request->validate([
            'attendance_id'   => 'required|exists:attendances,id',
            'after_check_in'  => 'required|date',
            'after_check_out' => 'required|date|after:after_check_in',
            'reason'          => 'required|max:255',
        ]);

        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        ShiftCorrection::create([
            'attendance_id'    => $attendance->id,
            'before_check_in'  => $attendance->check_in,
            'before_check_out' => $attendance->check_out,
            'after_check_in'   => $request->after_check_in,
            'after_check_out'  => $request->after_check_out,
            'reason'           => $request->reason,
        ]);

        return redirect('/correction')->with('success', '修正申請を送信しました');
    }
}