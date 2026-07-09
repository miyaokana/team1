<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\DakokuRequest;
use Illuminate\Support\Facades\Auth;

class DakokuRequestController extends Controller
{
    // 【ユーザー】修正申請画面の表示（履歴データも一緒に取得）
    public function createUserView(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $userId = Auth::id();

        // 現在登録されている確定データを取得
        $currentAttendance = Attendance::where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        // 現在「申請中」のデータがあるか確認
        $pendingRequest = DakokuRequest::where('user_id', $userId)
            ->where('date', $date)
            ->where('status', 'pending')
            ->first();

        // 💡【追加】このユーザーの過去の申請履歴をすべて取得（最新順）
        $historyRequests = DakokuRequest::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // compactの中に 'historyRequests' を追加
        return view('dakoku_requests.dakoku_requests', compact('date', 'currentAttendance', 'pendingRequest', 'historyRequests'));
    }

    // 【ユーザー】修正申請フォームの保存処理
    public function storeApplication(Request $request)
    {
        // 1. バリデーション（削除チェックがある場合、または時刻入力がある場合に理由を必須にする）
        $request->validate([
            'date'                => 'required|date',
            'delete_in'           => 'nullable|in:1',
            'delete_out'          => 'nullable|in:1',
            'requested_punch_in'  => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',
            
            // 💡 修正時刻がある、または削除チェックがある場合に理由を必須化
            'reason_in'           => 'required_if:delete_in,1|required_with:requested_punch_in|nullable|string|max:255',
            'reason_out'          => 'required_if:delete_out,1|required_with:requested_punch_out|nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $date = $request->date;
        $hasCreated = false;

        // 2. 出勤データの保存処理（修正、または削除）
        if ($request->filled('requested_punch_in') || $request->delete_in == 1) {
            $existsIn = DakokuRequest::where('user_id', $userId)
                ->where('date', $date)
                ->where('is_in_request', true) // 💡 出勤の申請であることを明示
                ->where('status', 'pending')
                ->exists();

            if (!$existsIn) {
                DakokuRequest::create([
                    'user_id'            => $userId,
                    'date'               => $date,
                    'is_in_request'      => true, // 💡 カラムを追加（推奨。なければ delete_in などをそのまま保存）
                    'is_delete'          => $request->delete_in == 1, // 💡 削除申請フラグ
                    'requested_punch_in' => $request->delete_in == 1 ? null : $request->requested_punch_in,
                    'reason'             => $request->reason_in,
                ]);
                $hasCreated = true;
            }
        }

        // 3. 退勤データの保存処理（修正、または削除）
        if ($request->filled('requested_punch_out') || $request->delete_out == 1) {
            $existsOut = DakokuRequest::where('user_id', $userId)
                ->where('date', $date)
                ->where('is_out_request', true)
                ->where('status', 'pending')
                ->exists();

            if (!$existsOut) {
                DakokuRequest::create([
                    'user_id'            => $userId,
                    'date'               => $date,
                    'is_in_request'      => false,
                    'is_out_request'     => true,
                    'is_delete'          => $request->delete_out == 1,
                    'requested_punch_in' => null,
                    'requested_punch_out'=> $request->delete_out == 1 ? null : $request->requested_punch_out,
                    'auto_break_out'     => $request->delete_out == 1 ? false : ($request->auto_break_out == 1), // 💡セレクトボックスの値を判定
                    'reason'             => $request->reason_out,
                ]);
                $hasCreated = true;
            }

        }

        if (!$hasCreated) {
            return back()->withErrors(['date' => 'すでに申請中か、有効な申請内容がありません。']);
        }

        return back()->with('success', '申請を提出しました。');
    }

    // 【管理者】承認または却下の判定処理
    public function adminApprove(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_comment' => 'nullable|string|max:255',
        ]);

        $dakokuRequest = DakokuRequest::findOrFail($id);

        if ($request->action === 'approve') {
            $dakokuRequest->update([
                'status' => 'approved',
                'admin_comment' => $request->admin_comment
            ]);

            // 本番の確定データを取得または作成
            $attendance = Attendance::firstOrNew([
                'user_id' => $dakokuRequest->user_id,
                'work_date' => $dakokuRequest->date
            ]);

            // 出勤の反映
            if ($dakokuRequest->is_in_request) {
                $attendance->check_in = $dakokuRequest->is_delete ? null : $dakokuRequest->requested_punch_in;
            }

            // 退勤の反映
            if ($dakokuRequest->is_out_request) {
                $attendance->check_out = $dakokuRequest->is_delete ? null : $dakokuRequest->requested_punch_out;
            }

            // 💡【休憩時間の自動計算ロジック】
            // 自動追加フラグが有効、かつ出勤・退勤のどちらもデータが存在する場合に計算
            if ($dakokuRequest->auto_break_out && $attendance->check_in && $attendance->check_out) {
                
                // Carbonで時間をパースして、滞在時間（分）を計算
                $inTime = \Carbon\Carbon::parse($attendance->check_in);
                $outTime = \Carbon\Carbon::parse($attendance->check_out);
                
                // 出退勤の差分（総労働分）を取得
                $workMinutes = $inTime->diffInMinutes($outTime);

                // 条件分岐（8時間=480分、7時間=420分、6時間=360分）
                if ($workMinutes >= 480) {
                    $attendance->break_minutes = 60;  // 8時間以上は60分
                } elseif ($workMinutes >= 420) {
                    $attendance->break_minutes = 45;  // 7時間以上は45分
                } elseif ($workMinutes >= 360) {
                    $attendance->break_minutes = 30;  // 6時間以上は30分
                } else {
                    $attendance->break_minutes = 0;   // 6時間未満は0分
                }
            }

            $attendance->save();

            return back()->with('success', '申請を承認し、勤怠データを更新しました。');
        }
    }
}