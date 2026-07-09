<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠修正 - {{ $user->user_name }}さん</title>
    <link rel="stylesheet" href="{{ asset('css/admin/attendance.css') }}">
</head>
<body>

<div class="admin-wrapper">

    <header class="top-bar">
        <h1>ギンクラ 管理画面</h1>
        <a href="/admin/users/{{ $user->id }}/attendance" class="nav-item">← 勤怠一覧に戻る</a>
    </header>

    <div class="container">

        <div class="header-area">
            <h2>
                {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }} の勤怠修正
            </h2>
            <span class="user-email-badge">{{ $user->user_name }} さん</span>
        </div>

        <div class="table-card form-card">
            <form action="/admin/attendance/{{ $attendance->id }}/update" method="POST" class="edit-attendance-form">
                @csrf

                <div class="form-row">
                    <div class="form-group-block flex-1">
                        <label class="form-label">出勤時刻</label>
                        <input type="time" name="check_in" value="{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '' }}" class="form-input">
                    </div>
                    <div class="form-group-block flex-1">
                        <label class="form-label">退勤時刻</label>
                        <input type="time" name="check_out" value="{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '' }}" class="form-input">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group-block flex-1">
                        <label class="form-label">休憩開始</label>
                        <input type="time" name="break_start" value="{{ $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '' }}" class="form-input">
                    </div>
                    <div class="form-group-block flex-1">
                        <label class="form-label">休憩終了</label>
                        <input type="time" name="break_end" value="{{ $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '' }}" class="form-input">
                    </div>
                </div>

                <div class="form-actions-row">
                    <button type="submit" class="submit-btn">変更を保存する</button>
                    <a href="/admin/users/{{ $user->id }}/attendance" class="cancel-btn">キャンセル</a>
                </div>

            </form>
        </div>

    </div>
</div>

</body>
</html>