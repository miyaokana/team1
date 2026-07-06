<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    // 自分の有給申請一覧＋フォーム(get /leave-requests)
    public function index() 
    {
        $requests = LeaveRequest::where('user_id', Auth::id())
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('leave_requests.index', [
            'requests' => $requests,
        ]);
    }

    // 有給申請の保存(POST /leave-requests)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:paid,special',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'day_type' => 'required|in:full_day,am,pm',
            'reason' => 'required|string|max:1000',
        ], [
            'type.required' => '種別を選択してください。',
            'start_date.required' => '開始日を入力してください。',
            'end_date.required' => '終了日を入力してください。',
            'day_type.required' => '取得区分を選択してください。',
            'reason.required' => '理由を入力してください。',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        // 制約1:終了日が開始日より前は不可
        if ($end->lt($start)) {
            return back()
                ->withInput()
                ->with('error', '終了日は開始日と同じか、それより後にしてください。');
        }

        // 制約2:半休(午前・午後)は1日単位。開始日と終了日が異なる場合は不可
        if (in_array($validated['day_type'], ['am', 'pm'], true) && !$start->eq($end)) {
            return back()
                ->withInput()
                ->with('error', '半休は1日だけ選べます。開始日と終了日を同じにしてください。');
        }

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'day_type' => $validated['day_type'],
            'reason' => $validated['reason'],
            'status' => 'pending', // 常に申請中で作成
        ]);

        return redirect()
            ->route('leave_requests.index')
            ->with('status', '有給申請を送信しました。');
    }
}