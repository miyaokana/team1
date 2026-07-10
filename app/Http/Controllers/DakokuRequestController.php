<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\DakokuRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class DakokuRequestController extends Controller
{
    // 【ユーザー】修正申請画面の表示
    public function createUserView(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $userId = Auth::id();

        $currentAttendance = Attendance::where('user_id', $userId)
            ->where('work_date', $date)
            ->first();

        $pendingRequest = DakokuRequest::where('user_id', $userId)
            ->where('date', $date)
            ->where('status', 'pending')
            ->first();

        $historyRequests = DakokuRequest::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view(
            'dakoku_requests.dakoku_requests',
            compact(
                'date',
                'currentAttendance',
                'pendingRequest',
                'historyRequests'
            )
        );
    }

    // 【ユーザー】修正申請保存
    public function storeApplication(Request $request)
    {
        $request->validate([
            'date'                => 'required|date',
            'delete_in'           => 'nullable|in:1',
            'delete_out'          => 'nullable|in:1',
            'requested_punch_in'  => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',

            'reason_in'  => 'required_if:delete_in,1|required_with:requested_punch_in|nullable|string|max:255',
            'reason_out' => 'required_if:delete_out,1|required_with:requested_punch_out|nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $date = $request->date;
        $hasCreated = false;

        // 出勤申請
        if ($request->filled('requested_punch_in') || $request->delete_in == 1) {

            $existsIn = DakokuRequest::where('user_id', $userId)
                ->where('date', $date)
                ->where('is_in_request', true)
                ->where('status', 'pending')
                ->exists();

            if (!$existsIn) {

                DakokuRequest::create([
                    'user_id'            => $userId,
                    'date'               => $date,
                    'is_in_request'      => true,
                    'is_out_request'     => false,
                    'is_delete'          => $request->delete_in == 1,
                    'requested_punch_in' => $request->delete_in == 1
                        ? null
                        : $request->requested_punch_in,
                    'reason'             => $request->reason_in,
                ]);

                $hasCreated = true;
            }
        }

        // 退勤申請
        if ($request->filled('requested_punch_out') || $request->delete_out == 1) {

            $existsOut = DakokuRequest::where('user_id', $userId)
                ->where('date', $date)
                ->where('is_out_request', true)
                ->where('status', 'pending')
                ->exists();

            if (!$existsOut) {

                DakokuRequest::create([
                    'user_id'             => $userId,
                    'date'                => $date,
                    'is_in_request'       => false,
                    'is_out_request'      => true,
                    'is_delete'           => $request->delete_out == 1,
                    'requested_punch_in'  => null,
                    'requested_punch_out' => $request->delete_out == 1
                        ? null
                        : $request->requested_punch_out,
                    'auto_break_out'      => $request->delete_out == 1
                        ? false
                        : ($request->auto_break_out == 1),
                    'reason'              => $request->reason_out,
                ]);

                $hasCreated = true;
            }
        }

        if (!$hasCreated) {
            return back()->withErrors([
                'date' => 'すでに申請中か、有効な申請内容がありません。'
            ]);
        }

        // 通知
        Notification::create([
            'user_id' => $userId,
            'title'   => '申請受付',
            'message' => $date . ' の打刻修正申請を受け付けました。'
        ]);

        return back()->with('success', '申請を提出しました。');
    }
}