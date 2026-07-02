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
        // すべての申請を最新順（created_atの降順）で取得。ユーザー情報(user)も一緒にロード(with)するやで！
        $attendanceRequests = AttendanceRequest::with('user')->orderBy('created_at', 'desc')->get();
        $leaveRequests      = LeaveRequest::with('user')->orderBy('created_at', 'desc')->get();
        $overtimeRequests   = OvertimeRequest::with('user')->orderBy('created_at', 'desc')->get();

        return view('admin.requests.index', compact('attendanceRequests', 'leaveRequests', 'overtimeRequests'));
    }
}