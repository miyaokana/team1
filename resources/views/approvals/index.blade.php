<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請の承認</title>
</head>
<body>
    <div class="layout">
        @include('layouts.sidebar')

        <div class="wrap">
            <h1>申請の承認</h1>

            @if (session('status'))
                <div class="flash flash-ok">{{ session('status') }}</div>
            @endif
            
            @if (session('error'))
                <div class="flash flash-err">{{ session('error') }}</div>
            @endif

            <!-- 1.遅刻・早退・欠勤 -->
            <div class="card">
                <h2>遅刻・早退・欠勤</h2>
                @if ($attendanceRequests->isEmpty())
                    <p>申請はありません</p>
                @else
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>申請者</th>
                                <th>種別</th>
                                <th>対象日</th>
                                <th>時刻</th>
                                <th>理由</th>
                                <th>添付</th>
                                <th>状態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendanceRequests as $req)
                                <tr>
                                    <td>{{ $req->user->user_name ?? '不明' }}</td>
                                    <td>{{ \App\Models\AttendanceRequest::TYPE_LABELS[$req->type] ?? $req->type }}</td>
                                    <td>{{ $req->target_date->format('n/j') }}</td>
                                    <td>{{ $req->request_time ? \Carbon\Carbon::parse($req->request_time)->format('H:i') : '-' }}</td>
                                    <td>{{ $req->reason }}</td>
                                    <td>
                                        @if ($req->attachment_path)
                                            <a href="{{ route('attendance_requests.attachment', $req) }}">表示</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\AttendanceRequest::STATUS_LABELS[$req->status] ?? $req->status }}</td>
                                    <td>@include('approvals.partials.actions', ['type' => 'attendance', 'req' => $req])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- 2.有給・特別休暇 -->
            <div class="card">
                <h2>有給・特別休暇</h2>
                @if ($leaveRequests->isEmpty())
                    <p>申請はありません。</p>
                @else
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>申請者</th>
                                <th>種別</th>
                                <th>期間</th>
                                <th>区分</th>
                                <th>理由</th>
                                <th>状態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveRequests as $req)
                                <tr>
                                    <td>{{ $req->user->user_name ?? '不明' }}</td>
                                    <td>{{ \App\Models\LeaveRequest::TYPE_LABELS[$req->type] ?? $req->type }}</td>
                                    <td>
                                        {{ $req->start_date->format('n/j') }}
                                        @if (!$req->start_date->eq($req->end_date))
                                            ~ {{ $req->end_date->format('n/j') }}
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\LeaveRequest::DAY_TYPE_LABELS[$req->day_type] ?? $req->day_type }}</td>
                                    <td>{{ $req->reason }}</td>
                                    <td>{{ \App\Models\LeaveRequest::STATUS_LABELS[$req->status] ?? $req->status }}</td>
                                    <td>@include('approvals.partials.actions', ['type' => 'leave', 'req' => $req])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- 3. 残業 -->
             <div class="card">
                <h2>残業</h2>
                @if ($overtimeRequests->isEmpty())
                    <p>申請はありません。</p>
                @else
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>申請者</th>
                                <th>対象日</th>
                                <th>時間</th>
                                <th>理由</th>
                                <th>状態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($overtimeRequests as $req)
                                <tr>
                                    <td>{{ $req->user->user_name ?? '不明' }}</td>
                                    <td>{{ $req->target_date->format('n/j') }}</td>
                                    <td>{{ $req->start_at->format('H:i') }} 
                                        ~ {{ $req->end_at->format('H:i') }}</td>
                                    <td>{{ $req->reason }}</td>
                                    <td>{{ \App\Models\OvertimeRequest::STATUS_LABELS[$req->status] ?? $req->status }}</td>
                                    <td>@include('approvals.partials.actions', ['type' => 'overtime', 'req' => $req])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
             </div>

        </div>
    </div>
    <script>
        function submitApproval(button, status) {
            const form = button.closest('form');
            form.querySelector('.status-input').value = status;

            if (status === 'rejected') {
                const reason = prompt('差し戻しの理由を入力してください :');
                if (reason === null) return;
                if (reason.trim() === ''){
                    alert('差し戻し理由は必須です。');
                    return;
                }
                form.querySelector('.comment-input').value = reason;
            } else {
                if (!confirm('この申請を承認しますか？')) return;
            }
            form.submit();
        }
    </script>
</body>
</html>