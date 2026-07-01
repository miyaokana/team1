<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>勤怠</title>

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>

<body>

<!-- 通知 -->
<div class="notice">
    <span class="badge">システム通知</span>
    「遅刻」発生通知（6月30日）
    <span class="confirm">確認</span>
</div>

<div class="wrap">

@php
$wp = ['日','月','火','水','木','金','土'][now()->dayOfWeek];
$isWorking = $status === '勤務中';
$isBreak = $status === '休憩中';
$notIn = $status === '未出勤';
@endphp

<!-- メインカード -->
<div class="card">

    <!-- 背景アニメ -->
    <div class="shape s1"></div>
    <div class="shape s2"></div>
    <div class="shape s3"></div>

    <!-- 状態 -->
    <div class="status-bar">
        ただいま {{ $status }}
    </div>

    <div class="main">

        <!-- 左 -->
        <div class="left">
            <div class="date">
                {{ now()->format('Y年n月j日') }} ({{ $wp }})
            </div>

            <div class="clock" id="clock">
                {{ now()->format('H:i') }}
                <span class="sec">{{ now()->format('s') }}</span>
            </div>

            <div class="user">
                {{ Auth::user()->user_name ?? Auth::user()->email }}
            </div>

            <div class="info">
                <div>勤務地：本社</div>

                <div>出勤：{{ optional($attendance?->check_in)->format('H:i') ?? '--:--' }}</div>
                <div>退勤：{{ optional($attendance?->check_out)->format('H:i') ?? '--:--' }}</div>

                <div>
                    休憩：
                    {{ optional($attendance?->break_start)->format('H:i') ?? '--:--' }}
                    〜
                    {{ optional($attendance?->break_end)->format('H:i') ?? '--:--' }}
                </div>

                <div>
                    勤務時間：
                    @if (!is_null($workMinutes))
                        {{ intdiv($workMinutes, 60) }}時間{{ $workMinutes % 60 }}分
                    @else
                        --
                    @endif
                </div>
            </div>
        </div>

        <!-- 右 -->
        <div class="right">

            <!-- 勤務地 -->
            <div class="location">
                <div class="location">
                    <span class="loc-label">勤務地</span>

                    <div class="loc-box">
                        <span>本社</span>
                        <span class="change-btn">変更</span>
                    </div>
                </div>
            </div>

            <!-- 出勤・退勤 -->
            <div class="punch-row">

                <!-- 出勤 -->
                <form action="{{ route('attendance.punch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="check_in">
                    <button class="big-btn in {{ !$notIn ? 'inactive' : '' }}"
                        {{ $notIn ? '' : 'disabled' }}>
                        出勤
                    </button>
                </form>

                <!-- 退勤 -->
                <form action="{{ route('attendance.punch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="check_out">
                    <button class="big-btn out {{ !$isWorking ? 'inactive' : '' }}"
                        {{ $isWorking ? '' : 'disabled' }}>
                        退勤
                    </button>
                </form>

            </div>

            <!-- トグル風 -->
            <div class="toggle">
                <span class="toggle-text">既定の休憩を追加</span>

                <label class="switch">
                    <input type="checkbox">
                    <span class="slider"></span>
                </label>
            </div>

            <!-- 休憩 -->
            <form action="{{ route('attendance.punch') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="break_start">
                <button class="sub-btn {{ !$isWorking ? 'inactive' : '' }}"
                    {{ $isWorking ? '' : 'disabled' }}>
                    休憩開始
                </button>
            </form>

            <!-- 下ボタン -->
            <div class="bottom-btns">
                <button class="outline">勤怠申請</button>
                <button class="outline">打刻修正</button>
            </div>

        </div>

    </div>
</div>

<!-- 打刻履歴 -->
<div class="history">
    <h3>打刻履歴</h3>

    <div class="history-item">
        <span class="tag">出勤</span>
        {{ optional($attendance?->check_in)->format('Y-m-d H:i') ?? '-' }}
    </div>

    <div class="history-item">
        <span class="tag">退勤</span>
        {{ optional($attendance?->check_out)->format('Y-m-d H:i') ?? '-' }}
    </div>
</div>

</div>

<!-- 時計 -->
<script>
setInterval(() => {
    const n = new Date();
    const p = x => String(x).padStart(2,'0');
    document.getElementById('clock').innerHTML =
        p(n.getHours()) + ':' + p(n.getMinutes()) +
        '<span class="sec">' + p(n.getSeconds()) + '</span>';
},1000);
</script>

</body>
</html>