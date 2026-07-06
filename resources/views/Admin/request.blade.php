<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>申請一覧・承認</title>
    <link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
</head>
<body>

<div class="admin-wrapper">

    <header class="top-bar">
        <h1><a href="/admin/users">ギンクラ 管理画面</a></h1>
        <nav class="admin-nav">
            <a href="/admin/users" class="nav-item">ユーザ一覧</a>
            <a href="/admin/requests" class="nav-item">📂 申請一覧・承認</a>
        </nav>
    </header>

    <div class="container">
        <h2>📂 ユーザーからの申請一覧</h2>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        <!-- ==================== 1. 遅刻・早退・欠勤申請 ==================== -->
        <h3>🕒 遅刻・早退・欠勤申請</h3>
        <table class="request-table">
            <thead>
                <tr>
                    <th>スタッフ名</th>
                    <th>種類</th>
                    <th>対象日</th>
                    <th>希望時刻</th>
                    <th>理由</th>
                    <th>ステータス</th>
                    <th>アクション</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceRequests as $req)
                <tr>
                    <td>{{ $req->user->user_name }}</td>
                    <td>
                        @if($req->type === 'late') 遅刻
                        @elseif($req->type === 'early_leave') 早退
                        @else 欠勤 @endif
                    </td>
                    <td>{{ $req->target_date }}</td>
                    <td>{{ $req->request_time ?? '-' }}</td>
                    <td>{{ $req->reason }}</td>
                    <td><strong>{{ $req->status }}</strong></td>
                    <td>
                        @if($req->status === 'pending')
                        <form action="{{ route('admin.requests.status', ['type' => 'attendance', 'id' => $req->id]) }}" method="POST" onsubmit="return handleStatusSubmit(this, event)">
                            @csrf
                            <!-- 隠しフィールドでコメントとステータスを送信する形に変更や！ -->
                            <input type="hidden" name="admin_comment" class="admin-comment-input">
                            <input type="hidden" name="status" class="status-input">
                            <button type="button" onclick="submitWithStatus(this, 'approved')">承認</button>
                            <button type="button" onclick="submitWithStatus(this, 'rejected')">差し戻し</button>
                        </form>
                        @else
                            {{ $req->admin_comment ?? 'コメントなし' }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- ==================== 2. 有給・特別休暇申請 ==================== -->
        <h3>📅 休暇申請（有給・特休）</h3>
        <table class="request-table">
            <thead>
                <tr>
                    <th>スタッフ名</th>
                    <th>種類</th>
                    <th>期間</th>
                    <th>区分</th>
                    <th>理由</th>
                    <th>ステータス</th>
                    <th>アクション</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaveRequests as $req)
                <tr>
                    <td>{{ $req->user->user_name }}</td>
                    <td>{{ $req->type === 'paid' ? '有給' : '特別休暇' }}</td>
                    <td>{{ $req->start_date }} 〜 {{ $req->end_date }}</td>
                    <td>
                        @if($req->day_type === 'full_day') 全日
                        @elseif($req->day_type === 'am') 午前半休
                        @else 午後半休 @endif
                    </td>
                    <td>{{ $req->reason }}</td>
                    <td><strong>{{ $req->status }}</strong></td>
                    <td>
                        @if($req->status === 'pending')
                        <form action="{{ route('admin.requests.status', ['type' => 'leave', 'id' => $req->id]) }}" method="POST" onsubmit="return handleStatusSubmit(this, event)">
                            @csrf
                            <input type="hidden" name="admin_comment" class="admin-comment-input">
                            <input type="hidden" name="status" class="status-input">
                            <button type="button" onclick="submitWithStatus(this, 'approved')">承認</button>
                            <button type="button" onclick="submitWithStatus(this, 'rejected')">差し戻し</button>
                        </form>
                        @else
                            {{ $req->admin_comment ?? 'コメントなし' }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- ==================== 3. 残業申請 ==================== -->
        <h3>💪 残業申請</h3>
        <table class="request-table">
            <thead>
                <tr>
                    <th>スタッフ名</th>
                    <th>対象日</th>
                    <th>時間範囲</th>
                    <th>理由</th>
                    <th>ステータス</th>
                    <th>アクション</th>
                </tr>
            </thead>
            <tbody>
                @foreach($overtimeRequests as $req)
                <tr>
                    <td>{{ $req->user->user_name }}</td>
                    <td>{{ $req->target_date }}</td>
                    <td>{{ Carbon\Carbon::parse($req->start_at)->format('H:i') }} 〜 {{ Carbon\Carbon::parse($req->end_at)->format('H:i') }}</td>
                    <td>{{ $req->reason }}</td>
                    <td><strong>{{ $req->status }}</strong></td>
                    <td>
                        @if($req->status === 'pending')
                        <form action="{{ route('admin.requests.status', ['type' => 'overtime', 'id' => $req->id]) }}" method="POST" onsubmit="return handleStatusSubmit(this, event)">
                            @csrf
                            <input type="hidden" name="admin_comment" class="admin-comment-input">
                            <input type="hidden" name="status" class="status-input">
                            <button type="button" onclick="submitWithStatus(this, 'approved')">承認</button>
                            <button type="button" onclick="submitWithStatus(this, 'rejected')">差し戻し</button>
                        </form>
                        @else
                            {{ $req->admin_comment ?? 'コメントなし' }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>

<!-- 🌟 ポップアップ制御用のJavaScriptや！ -->
<script>
function submitWithStatus(button, status) {
    const form = button.closest('form');
    form.querySelector('.status-input').value = status;

    if (status === 'rejected') {
        // 差し戻しの時だけポップアップ（prompt）を出すで！
        const reason = prompt('差し戻しの理由を入力してください：');
        
        // キャンセルされたか、何も入力されへんかったら送信をストップ！
        if (reason === null) {
            return;
        }
        if (reason.trim() === '') {
            alert('差し戻し理由は必須やで！');
            return;
        }
        
        // 入力された理由を隠しフィールドにセット
        form.querySelector('.admin-comment-input').value = reason;
    } else {
        // 承認の時は確認のダイアログだけ出す（不要なら消してOKや！）
        if (!confirm('この申請を承認してもよろしいですか？')) {
            return;
        }
    }

    // 条件をクリアしたらフォームを送信！
    form.submit();
}
</script>

</body>
</html>