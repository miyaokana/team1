<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請の承認</title>
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/approvals.css') }}">
</head>

<body>

    <div class="admin-wrapper">

        <header class="top-bar">
            <div class="brand-area">
                <h1>ギンクラ 管理画面</h1>
                <span class="company-badge">{{ $companyName }}</span>
            </div>
            <div class="nav-links">
                <a href="{{ route('approvals.index') }}" class="nav-item">申請一覧・承認</a>
                <a href="{{ route('login') }}" class="nav-item back-btn">ログイン画面へ戻る</a>
            </div>
        </header>

        <div class="wrap">
            <h1>申請の承認</h1>

            @if (session('status'))
            <div class="flash flash-ok">{{ session('status') }}</div>
            @endif

            @if (session('error'))
            <div class="flash flash-err">{{ session('error') }}</div>
            @endif

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
                            <td class="user-name">{{ $req->user->user_name ?? '不明' }}</td>
                            <td>
                                @php
                                $badgeClass = 'absent';
                                if (($req->type ?? '') === 'late') $badgeClass = 'late';
                                elseif (($req->type ?? '') === 'early_leave') $badgeClass = 'early';
                                @endphp
                                <span class="type-badge {{ $badgeClass }}">
                                    {{ \App\Models\AttendanceRequest::TYPE_LABELS[$req->type] ?? $req->type }}
                                </span>
                            </td>
                            <td class="date-text">{{ $req->target_date->format('n/j') }}</td>
                            <td class="time-text">{{ $req->request_time ? \Carbon\Carbon::parse($req->request_time)->format('H:i') : '-' }}</td>
                            <td class="reason-text">{{ $req->reason }}</td>
                            <td>
                                @if ($req->attachment_path)
                                <a href="{{ route('attendance_requests.attachment', $req) }}" class="user-link">表示</a>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $req->status }}">
                                    {{ \App\Models\AttendanceRequest::STATUS_LABELS[$req->status] ?? $req->status }}
                                </span>
                            </td>
                            <td>@include('approvals.partials.actions', ['type' => 'attendance', 'req' => $req])</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

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
                            <td class="user-name">{{ $req->user->user_name ?? '不明' }}</td>
                            <td>
                                <span class="type-badge {{ ($req->type ?? '') === 'paid' ? 'paid-leave' : 'special-leave' }}">
                                    {{ \App\Models\LeaveRequest::TYPE_LABELS[$req->type] ?? $req->type }}
                                </span>
                            </td>
                            <td class="date-text">
                                {{ $req->start_date->format('n/j') }}
                                @if (!$req->start_date->eq($req->end_date))
                                ~ {{ $req->end_date->format('n/j') }}
                                @endif
                            </td>
                            <td>{{ \App\Models\LeaveRequest::DAY_TYPE_LABELS[$req->day_type] ?? $req->day_type }}</td>
                            <td class="reason-text">{{ $req->reason }}</td>
                            <td>
                                <span class="status-badge {{ $req->status }}">
                                    {{ \App\Models\LeaveRequest::STATUS_LABELS[$req->status] ?? $req->status }}
                                </span>
                            </td>
                            <td>@include('approvals.partials.actions', ['type' => 'leave', 'req' => $req])</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

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
                            <td class="user-name">{{ $req->user->user_name ?? '不明' }}</td>
                            <td class="date-text">{{ $req->target_date->format('n/j') }}</td>
                            <td class="time-text">{{ $req->start_at->format('H:i') }} ~ {{ $req->end_at->format('H:i') }}</td>
                            <td class="reason-text">{{ $req->reason }}</td>
                            <td>
                                <span class="status-badge {{ $req->status }}">
                                    {{ \App\Models\OvertimeRequest::STATUS_LABELS[$req->status] ?? $req->status }}
                                </span>
                            </td>
                            <td>@include('approvals.partials.actions', ['type' => 'overtime', 'req' => $req])</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- 打刻修正カードを .wrap の中に移動 --}}
            <div class="card">
                <h2>打刻修正申請</h2>

                @if ($dakokuRequests->isEmpty())
                <p>申請はありません。</p>
                @else
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>申請者</th>
                            <th>日付</th>
                            <th>種別</th>
                            <th>申請時刻</th>
                            <th>理由</th>
                            <th>状態</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dakokuRequests as $req)
                        <tr>
                            <td class="user-name">{{ $req->user->user_name ?? $req->user->name }}</td>
                            <td class="date-text">{{ $req->date }}</td>
                            <td>
                                @if($req->is_in_request)
                                出勤
                                @elseif($req->is_out_request)
                                退勤
                                @endif

                                @if($req->is_delete)
                                （削除）
                                @endif
                            </td>
                            <td class="time-text">
                                @if($req->is_in_request)
                                {{ $req->requested_punch_in ?? '削除' }}
                                @else
                                {{ $req->requested_punch_out ?? '削除' }}
                                @endif
                            </td>
                            <td class="reason-text">{{ $req->reason }}</td>
                            <td>
                                <span class="status-badge {{ $req->status }}">{{ $req->status }}</span>
                            </td>
                            <td>
                                {{-- form 開始タグを追加（元HTMLでは欠落していた） --}}
                                <form method="POST" action="{{ route('admin.dakoku.requests.approve', $req->id) }}">
                                    @csrf

                                    <input
                                        type="text"
                                        name="admin_comment"
                                        placeholder="コメント（任意）">

                                    <br><br>

                                    <button
                                        type="submit"
                                        name="action"
                                        value="approve"
                                        class="approve-btn">
                                        承認
                                    </button>

                                    <button
                                        type="submit"
                                        name="action"
                                        value="reject"
                                        class="reject-btn">
                                        却下
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

        </div>{{-- /.wrap --}}
    </div>{{-- /.admin-wrapper --}}

    <script>
        function submitApproval(button, status) {
            const form = button.closest('form');
            form.querySelector('.status-input').value = status;

            if (status === 'rejected') {
                const reason = prompt('差し戻しの理由を入力してください :');
                if (reason === null) return;
                if (reason.trim() === '') {
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