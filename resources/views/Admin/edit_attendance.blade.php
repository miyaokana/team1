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
            <h2>勤怠データの修正</h2>
            <span class="user-email-badge">{{ $user->user_name }} さん</span>
        </div>

        <div class="table-card" style="max-width: 600px; margin: 0 auto;">
            <form action="/admin/attendance/{{ $attendance->id }}/update" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                @csrf

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label style="font-weight: bold; color: #555;">勤務日</label>
                    <input type="date" name="work_date" value="{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d') }}" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px;">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 5px;">
                        <label style="font-weight: bold; color: #555;">出勤時刻</label>
                        <input type="time" name="check_in" value="{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '' }}" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px;">
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 5px;">
                        <label style="font-weight: bold; color: #555;">退勤時刻</label>
                        <input type="time" name="check_out" value="{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '' }}" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px;">
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 5px;">
                        <label style="font-weight: bold; color: #555;">休憩開始</label>
                        <input type="time" name="break_start" value="{{ $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '' }}" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px;">
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 5px;">
                        <label style="font-weight: bold; color: #555;">休憩終了</label>
                        <input type="time" name="break_end" value="{{ $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '' }}" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px;">
                    </div>
                </div>

                <div style="margin-top: 10px; display: flex; gap: 10px;">
                    <button type="submit" style="flex: 1; padding: 12px; background: #ff6b6b; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer;">変更を保存する</button>
                    <a href="/admin/users/{{ $user->id }}/attendance" style="padding: 12px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-size: 16px; text-align: center;">キャンセル</a>
                </div>

            </form>
        </div>

    </div>
</div>

</body>
</html>