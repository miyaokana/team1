<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\DakokuRequest; // 新しいモデル
use Illuminate\Support\Facades\Auth;;

class DakokuRequestController extends Controller
{
    // 【ユーザー】修正申請画面の表示
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

        return view('dakoku_requests.dakoku_requests', compact('date', 'currentAttendance', 'pendingRequest'));
    }

    // 【ユーザー】修正申請フォームの保存処理
    public function storeApplication(Request $request)
    {
        // 1. バリデーション（入力がある場合のみ必須にする）
        $request->validate([
            'date'                => 'required|date',
            'requested_punch_in'  => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',
            'reason_in'           => 'required_with:requested_punch_in|nullable|string|max:255',
            'reason_out'          => 'required_with:requested_punch_out|nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $date = $request->date;
        $hasCreated = false;

        // 2. 出勤データの保存処理
        if ($request->filled('requested_punch_in')) {
            $existsIn = DakokuRequest::where('user_id', $userId)
                ->where('date', $date)
                ->whereNotNull('requested_punch_in')
                ->where('status', 'pending')
                ->exists();

            if (!$existsIn) {
                DakokuRequest::create([
                    'user_id'            => $userId,
                    'date'               => $date,
                    'requested_punch_in' => $request->requested_punch_in,
                    'requested_punch_out'=> null,
                    'reason'             => $request->reason_in, // 💡 出勤の理由を保存
                ]);
                $hasCreated = true;
            }
        }

        // 3. 退勤データの保存処理
        if ($request->filled('requested_punch_out')) {
            $existsOut = DakokuRequest::where('user_id', $userId)
                ->where('date', $date)
                ->whereNotNull('requested_punch_out')
                ->where('status', 'pending')
                ->exists();

            if (!$existsOut) {
                DakokuRequest::create([
                    'user_id'            => $userId,
                    'date'               => $date,
                    'requested_punch_in' => null,
                    'requested_punch_out'=> $request->requested_punch_out,
                    'reason'             => $request->reason_out, // 💡 退勤の理由を保存
                ]);
                $hasCreated = true;
            }
        }

        if (!$hasCreated) {
            return back()->withErrors(['date' => 'すでに申請中か、有効な修正時間が入力されていません。']);
        }

        return back()->with('success', '修正申請を個別に提出しました。');
    }

    // 【管理者】申請一覧画面の表示
    public function adminIndex()
    {
        $requests = DakokuRequest::with('user')->where('status', 'pending')->orderBy('created_at', 'desc')->get();
        return view('admin.dakoku', compact('requests'));
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
            // 1. 承認済みに更新
            $dakokuRequest->update([
                'status' => 'approved',
                'admin_comment' => $request->admin_comment
            ]);

            // 2. 本番の確定テーブルにデータを反映
            Attendance::updateOrCreate(
                [
                    'user_id' => $dakokuRequest->user_id,
                    'work_date' => $dakokuRequest->date
                ],
                [
                    'check_in' => $dakokuRequest->requested_punch_in,
                    'check_out' => $dakokuRequest->requested_punch_out,
                ]
            );

            return back()->with('success', '申請を承認し、勤怠データを更新しました。');

        } else {
            // 却下処理
            $dakokuRequest->update([
                'status' => 'rejected',
                'admin_comment' => $request->admin_comment
            ]);

            return back()->with('success', '申請を却下しました。');
        }
    }
}