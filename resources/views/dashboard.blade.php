<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>勤怠</title>

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>

<body>

<!-- ✅ 通知 -->
<div class="notice">
    <span class="badge">システム通知</span>
    「遅刻」発生通知（6月30日）
    <span class="confirm">確認</span>
</div>

<div class="wrap">

<!-- ✅ フラッシュ -->
@if (session('status'))
    <div class="flash ok">{{ session('status') }}</div>
@endif

@if (session('error'))
    <div class="flash err">{{ session('error') }}</div>
@endif

@php
$wp = ['日','月','火','水','木','金','土'][now()->dayOfWeek];
$isWorking = $status === '勤務中';
$isBreak = $status === '休憩中';
$notIn = $status === '未出勤';
@endphp

<!-- ✅ メインカード -->
<div class="card">

    <!-- 背景アニメーション -->
    <div class="shape s1"></div>
    <div class="shape s2"></div>
    <div class="shape s3"></div>

    <!-- 状態 -->
    <div class="status-bar">
        ただいま {{ $status }}
    </div>

    <div class="main-flex">

        <!-- 左 -->
        <div class="left">

            <div class="date">
                {{ now()->format('Y年n月j日') }} ({{ $wp }})
            </div>

            <div class="clock" id="clock">
                {{ now()->format('H:i:s') }}
            </div>

            <div class="user">
                {{ Auth::user()->user_name ?? Auth::user()->email }}
            </div>

            <!-- ✅ 勤務情報（全部そのまま） -->
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

            <!-- ✅ 出勤 -->
            <form action="{{ route('attendance.punch') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="check_in">
                <button class="btn in {{ !$notIn ? 'inactive' : '' }}" {{ $notIn ? '' : 'disabled' }}>
                    出勤
                </button>
            </form>

            <!-- ✅ 退勤（状態で色変わる） -->
            <form action="{{ route('attendance.punch') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="check_out">
                <button class="btn out {{ !$isWorking ? 'inactive' : '' }}" {{ $isWorking ? '' : 'disabled' }}>
                    退勤
                </button>
            </form>

            <!-- ✅ 休憩開始 -->
            <form action="{{ route('attendance.punch') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="break_start">
                <button class="btn sub {{ !$isWorking ? 'inactive' : '' }}" {{ $isWorking ? '' : 'disabled' }}>
                    休憩開始
                </button>
            </form>

            <!-- ✅ 休憩終了 -->
            <form action="{{ route('attendance.punch') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="break_end">
                <button class="btn sub {{ !$isBreak ? 'inactive' : '' }}" {{ $isBreak ? '' : 'disabled' }}>
                    休憩終了
                </button>
            </form>

            <!-- ✅ 追加ボタン -->
            <div class="mini-row">
                <button class="mini">勤怠申請</button>
                <button class="mini">打刻修正</button>
            </div>

        </div>
    </div>
</div>

<!-- ✅ 打刻履歴 -->
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

<!-- ✅ 時計 -->
<script>
setInterval(() => {
    const n = new Date();
    const p = x => String(x).padStart(2,'0');
    document.getElementById('clock').textContent =
        p(n.getHours()) + ':' + p(n.getMinutes()) + ':' + p(n.getSeconds());
},1000);
</script>

</body>
</html>