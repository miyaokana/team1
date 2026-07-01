<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    // 自分の申請一覧＋フォーム(GET /attendance-requests)
    public function index()
    {
        $requests = AttendanceRequest::where('user_id', Auth::id())
            ->orderBy('target_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('attendance_requests.index', [
            'requests' => $requests,
        ]);
    }

    // 申請の保存(POST /attendance-requests)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:late,early_leave,absence',
            'target_date' => 'required|date',
            // 遅刻・早退の時だけ時刻必須。欠勤は不要
            'request_time' => 'required_if:type,late,early_leave|nullable|date_format:H:i',
            'reason' => 'required|string|max:1000',
        ],[
            'type.required' => '申請種別を選択してください。',
            'target_date.required' => '対象日を入力してください。',
            'request_time.required_if' => '遅刻・早退の場合は時刻を入力してください。',
            'request_time.date_format' => '時刻はHH:MM形式で入力してください。',
            'reason.required' => '理由を入力してください。',
        ]);

        // 欠勤の時は時刻を保存しない。
        $requestTime = $validated['type'] === 'absence'
            ? null
            : $validated['request_time'];


        AttendanceRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'target_date' => $validated['target_date'],
            'request_time' => $requestTime,
            'reason' => $validated['reason'],
            'status' => 'pending', // 常に申請中で作成
        ]);

        return redirect()
            ->route('attendance_requests.index')
            ->with('status', '申請を送信しました。');
    }
}