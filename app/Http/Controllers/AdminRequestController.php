<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * 申請一覧画面を表示する
     */
    public function index()
    {
        // すべての申請を最新順で取得。ユーザー情報(user)も一緒にロードするやで！
        $attendanceRequests = AttendanceRequest::with('user')->orderBy('created_at', 'desc')->get();
        $leaveRequests      = LeaveRequest::with('user')->orderBy('created_at', 'desc')->get();
        $overtimeRequests   = OvertimeRequest::with('user')->orderBy('created_at', 'desc')->get();

        return view('admin.requests.index', compact('attendanceRequests', 'leaveRequests', 'overtimeRequests'));
    }

    /** 
     * 🌟 ここを追加！申請の承認・差し戻しステータスをアップデートする
     */
    public function updateStatus(Request $request, $type, $id)
    {
        // 1. バリデーションチェック（statusはapprovedかrejectedのみ、コメントは任意）
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_comment' => 'nullable|string|max:1000',
        ]);

        // 2. 申請タイプ（type）に応じて対象のモデルをチョイス（選択）するで！
        switch ($type) {
            case 'attendance':
                $model = AttendanceRequest::find($id);
                break;
            case 'leave':
                $model = LeaveRequest::find($id);
                break;
            case 'overtime':
                $model = OvertimeRequest::find($id);
                break;
            default:
                return redirect()->back()->with('error', '無効な申請タイプやで！');
        }

        // 3. もしデータが見つからへんかったらエラーでリターン
        if (!$model) {
            return redirect()->back()->with('error', '申請データが見つかりまへんでした。');
        }

        // 4. DBの値をアップデート（保存）するんや！
        $model->status = $validated['status'];
        $model->admin_comment = $validated['admin_comment'];
        $model->save();

        // 5. 画面に「成功メッセージ」を引っ提げてリダイレクトバック！
        $statusText = $validated['status'] === 'approved' ? '承認' : '差し戻し';
        return redirect()->back()->with('success', "申請を{$statusText}したで！");
    }
}