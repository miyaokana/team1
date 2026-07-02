<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeRequestController extends Controller
{
    // 自分の残業申請一覧＋フォーム(get /overtime-requests)
    public function index()
    {
        $requests = OvertimeRequest::where('user_id', Auth::id())
            ->orderBy('target_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('overtime_requests.index', [
            'requests' => $requests,
        ]);
    }

    // 残業申請の保存(post /ovetime-requests)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:1000',
        ],[
            'target_date.required' => '対象日を入力してください。',
            'start_time.required' => '開始時刻を入力してください。',
            'start_time.date_format' => '開始時刻はHH:MM形式で入力してください。',
            'end_time.required' => '終了時刻を入力してください。',
            'end_time.date_format' => '終了し事項はHH:MM形式で入力してください。',
            'reason.required' => '理由を入力してください。',
        ]);

        // 対象日＋時刻を結合して開始・終了のdateTimeを作る。
        $startAt = Carbon::parse($validated['target_date'] . ' ' . $validated['start_time']);
        $endAt = Carbon::parse($validated['target_date'] . ' ' . $validated['end_time']);

        // 深夜またぎの処理。終了が開始より前なら翌日、同じ時間ならエラー
        if ($endAt->lt($startAt)){
            $endAt -> addDay();
        } elseif ($endAt->eq($startAt)){
            return back()
                ->withInput()
                ->with('error', '開始時刻と終了時刻が同じです。時刻を確認してください。');
        }

        OvertimeRequest::create([
            'user_id' => Auth::id(),
            'target_date' => $validated['target_date'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'reason' => $validated['reason'],
            'status' => 'pending', // 常に申請中で作成。承認例には触れない。
        ]);

        return redirect()
            ->route('overtime_requests.index')
            ->with('status', '残業申請を送信しました。');

    }
}