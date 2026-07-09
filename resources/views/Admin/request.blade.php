<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>申請一覧・承認</title>
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">
</head>
<body>

<div class="admin-wrapper">

    <header class="top-bar">
        <h1><a href="/admin/users">ギンクラ 管理画面</a></h1>
        <nav class="admin-nav">
            <a href="/admin/users" class="nav-item">ユーザ一覧</a>
            <a href="/admin/requests" class="nav-item active">📂 申請一覧・承認</a>
        </nav>
    </header>

    <div class="container">
        <h2 class="page-title">📂 ユーザーからの申請一覧</h2>

        @if(session('success'))
            <div class="alert success">✨ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert error">⚠️ {{ session('error') }}</div>
        @endif

        <h3 class="section-title">🕒 遅刻・早退・欠勤申請</h3>
        <div class="table-responsive">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>スタッフ名</th>
                        <th>種類</th>
                        <th>対象日</th>
                        <th>希望時刻</th>
                        <th>理由</th>
                        <th>ステータス</th>
                        <th class="text-center">アクション</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendanceRequests as $req)
                    <tr>
                        <td class="user-name">{{ $req->user->user_name }}</td>
                        <td>
                            @if($req->type === 'late') <span class="type-badge late">遅刻</span>
                            @elseif($req->type === 'early_leave') <span class="type-badge early">早退</span>
                            @else <span class="type-badge absent">欠勤</span> @endif
                        </td>
                        <td class="date-text">{{ $req->target_date }}</td>
                        <td class="time-text">{{ $req->request_time ?? '-' }}</td>
                        <td class="reason-text">{{ $req->reason }}</td>
                        <td><span class="status-badge {{ $req->status }}">{{ $req->status }}</span></td>
                        <td class="actions-cell text-center">
                            @if($req->status === 'pending')
                            <form action="{{ route('admin.requests.status', ['type' => 'attendance', 'id' => $req->id]) }}" method="POST" onsubmit="return handleStatusSubmit(this, event)">
                                @csrf
                                <input type="hidden" name="admin_comment" class="admin-comment-input">
                                <input type="hidden" name="status" class="status-input">
                                <button type="button" class="btn-approve" onclick="submitWithStatus(this, 'approved')">承認</button>
                                <button type="button" class="btn-reject" onclick="submitWithStatus(this, 'rejected')">差し戻し</button>
                            </form>
                            @else
                                <span class="admin-comment">{{ $req->admin_comment ?? 'コメントなし' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3 class="section-title">📅 休暇申請（有給・特休）</h3>
        <div class="table-responsive">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>スタッフ名</th>
                        <th>種類</th>
                        <th>期間</th>
                        <th>区分</th>
                        <th>理由</th>
                        <th>ステータス</th>
                        <th class="text-center">アクション</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveRequests as $req)
                    <tr>
                        <td class="user-name">{{ $req->user->user_name }}</td>
                        <td>
                            <span class="type-badge {{ $req->type === 'paid' ? 'paid-leave' : 'special-leave' }}">
                                {{ $req->type === 'paid' ? '有給' : '特別休暇' }}
                            </span>
                        </td>
                        <td class="date-text">{{ $req->start_date }} 〜 {{ $req->end_date }}</td>
                        <td>
                            @if($req->day_type === 'full_day') 全日
                            @elseif($req->day_type === 'am') 午前半休
                            @else 午後半休 @endif
                        </td>
                        <td class="reason-text">{{ $req->reason }}</td>
                        <td><span class="status-badge {{ $req->status }}">{{ $req->status }}</span></td>
                        <td class="actions-cell text-center">
                            @if($req->status === 'pending')
                            <form action="{{ route('admin.requests.status', ['type' => 'leave', 'id' => $req->id]) }}" method="POST" onsubmit="return handleStatusSubmit(this, event)">
                                @csrf
                                <input type="hidden" name="admin_comment" class="admin-comment-input">
                                <input type="hidden" name="status" class="status-input">
                                <button type="button" class="btn-approve" onclick="submitWithStatus(this, 'approved')">承認</button>
                                <button type="button" class="btn-reject" onclick="submitWithStatus(this, 'rejected')">差し戻し</button>
                            </form>
                            @else
                                <span class="admin-comment">{{ $req->admin_comment ?? 'コメントなし' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h3 class="section-title">💪 残業申請</h3>
        <div class="table-responsive">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>スタッフ名</th>
                        <th>対象日</th>
                        <th>時間範囲</th>
                        <th>理由</th>
                        <th>ステータス</th>
                        <th class="text-center">アクション</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overtimeRequests as $req)
                    <tr>
                        <td class="user-name">{{ $req->user->user_name }}</td>
                        <td class="date-text">{{ $req->target_date }}</td>
                        <td class="time-text">{{ Carbon\Carbon::parse($req->start_at)->format('H:i') }} 〜 {{ Carbon\Carbon::parse($req->end_at)->format('H:i') }}</td>
                        <td class="reason-text">{{ $req->reason }}</td>
                        <td><span class="status-badge {{ $req->status }}">{{ $req->status }}</span></td>
                        <td class="actions-cell text-center">
                            @if($req->status === 'pending')
                            <form action="{{ route('admin.requests.status', ['type' => 'overtime', 'id' => $req->id]) }}" method="POST" onsubmit="return handleStatusSubmit(this, event)">
                                @csrf
                                <input type="hidden" name="admin_comment" class="admin-comment-input">
                                <input type="hidden" name="status" class="status-input">
                                <button type="button" class="btn-approve" onclick="submitWithStatus(this, 'approved')">承認</button>
                                <button type="button" class="btn-reject" onclick="submitWithStatus(this, 'rejected')">差し戻し</button>
                            </form>
                            @else
                                <span class="admin-comment">{{ $req->admin_comment ?? 'コメントなし' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
function submitWithStatus(button, status) {
    const form = button.closest('form');
    form.querySelector('.status-input').value = status;

    if (status === 'rejected') {
        const reason = prompt('差し戻しの理由を入力してください：');
        if (reason === null) return;
        if (reason.trim() === '') {
            alert('差し戻し理由は必須です');
            return;
        }
        form.querySelector('.admin-comment-input').value = reason;
    } else {
        if (!confirm('この申請を承認してもよろしいですか？')) return;
    }
    form.submit();
}
</script>

</body>
</html>