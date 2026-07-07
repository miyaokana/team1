<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            // 添付は任意。写真やPDFのみ、5MB(5120KB)まで
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ],[
            'type.required' => '申請種別を選択してください。',
            'target_date.required' => '対象日を入力してください。',
            'request_time.required_if' => '遅刻・早退の場合は時刻を入力してください。',
            'request_time.date_format' => '時刻はHH:MM形式で入力してください。',
            'reason.required' => '理由を入力してください。',
            'attachment.mimes' => '添付は写真(JPG/PNG)またはPDFのみです。',
            'attachment.max' => '添付ファイルは5MBまでです。',
        ]);

        // 欠勤の時は時刻を保存しない。
        $requestTime = $validated['type'] === 'absence'
            ? null
            : $validated['request_time'];
        
        // 添付ファイルがあれば非公開ディスクに保存し、そのパスを控える
        $attachmentPath = null;
        if($request->hasFile('attachment')){
            // storage/app/private/attendance_requests 配下に保存
            $attachmentPath = $request->file('attachment')
                ->store('attendance_requests', 'local');
        }

        AttendanceRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'target_date' => $validated['target_date'],
            'request_time' => $requestTime,
            'reason' => $validated['reason'],
            'status' => 'pending', // 常に申請中で作成
            'attachment_path' => $attachmentPath,
        ]);

        return redirect()
            ->route('attendance_requests.index')
            ->with('status', '申請を送信しました。');
    }

    // 添付ファイルのダウンロード(GET /attendance-requests/{id}/attachment)
    public function downloadAttachment(AttendanceRequest $attendanceRequest)
    {
        // 本人または管理者(role=1)のみ許可。それ以外は403
        $user = Auth::user();
        $isOwner = $attendanceRequest->user_id === $user->id;
        $isAdmin = $user->role === 1;

        if (!$isOwner && !$isAdmin) {
            abort(403, 'この添付ファイルを閲覧する権限がありません。');
        }

        // 添付がない、または実ファイルが存在しない場合は404
        if (!$attendanceRequest->attachment_path
            || !Storage::disk('local')->exists($attendanceRequest->attachment_path)) {
            abort(404, '添付ファイルが見つかりません。');
        }

        // 非公開ディスクから認可済みで返す
        return Storage::disk('local')->download($attendanceRequest->attachment_path);
    }
}