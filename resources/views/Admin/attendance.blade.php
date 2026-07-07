<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->user_name }}さんの勤怠一覧</title>
    <link rel="stylesheet" href="{{ asset('css/admin/attendance.css') }}">
</head>
<body>

<div class="admin-wrapper">

    <header class="top-bar">
        <h1>ギンクラ 管理画面</h1>
        <div class="nav-links">
            <a href="/admin/users" class="nav-item">← ユーザ一覧に戻る</a>
            <a href="/admin/requests" class="nav-item">申請一覧</a>
            <a href="{{ route('approvals.index') }}" class="nav-item">申請一覧・承認</a>
        </div>
    </header>

    <div class="container">

        <div class="header-area">
            <h2>{{ $user->user_name }} さんの勤怠一覧</h2>
            <span class="user-email-badge">{{ $user->email }}</span>
        </div>

        <div class="table-card">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>勤務日</th>
                        <th>出勤時刻</th>
                        <th>退勤時刻</th>
                        <th>休憩開始</th>
                        <th>休憩終了</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td class="work-date">
                            {{ $attendance->work_date instanceof \Carbon\Carbon ? $attendance->work_date->format('Y-m-d') : $attendance->work_date }}
                        </td>
                        
                        <td class="time-cell in">
                            {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '--:--' }}
                        </td>
                        
                        <td class="time-cell out">
                            {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '--:--' }}
                        </td>
                        
                        <td class="time-cell break">
                            {{ $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '--:--' }}
                        </td>
                        
                        <td class="time-cell break">
                            {{ $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '--:--' }}
                        </td>

                        <td class="actions">
                            <a href="/admin/attendance/{{ $attendance->id }}/edit" class="edit-btn">修正</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-message">勤怠データがまだレジスト（登録）されていません。</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>