<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠ダッシュボード</title>
</head>
<body>
<div class="wrap">
    <h1>勤怠ダッシュボード</h1>

    <!-- 打刻完了 / エラーメッセージ -->
     @if (session('status'))
        <div class="flash flash-ok">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="flash flash-err">{{ session('error') }}</div>
    @endif

    <!-- ユーザ名・現在日時 -->
     @php
        $wp = ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek];
    @endphp
    <div class="card">
        <div class="date">{{ now()->format('Y年n月j日') }} ({{ $wp }}) </div>
        <div class="clock" id="clock">{{ now()->format('H:i:s') }}</div>
        <div class="row">
            <span class="label">ログインユーザ</span>
            <span class="value">{{ Auth::user()->user_name ?? Auth::user()->email }}</span>
        </div>
        <div class="row">
            <span class="label">本日の状況</span>
            <span class="status-badge">{{ $status }}</span>
        </div>
    </div>

    <!-- 本日の打刻状況・勤務時間 -->
     <div class="card">
        <div class="row">
            <span class="label">出勤</span>
            <span class="value">{{ optional($attendance?->check_in)->format('H:i') ?? '--:--'  }}</span>
        </div>
        <div class="row">
            <span class="label">退勤</span>
            <span class="value">{{ optional($attendance?->check_out)->format('H:i') ?? '--:--' }}</span>
        </div>
        <div class="row">
            <span class="label">休憩</span>
            <span class="value">
                {{ optional($attendance?->break_start)->format('H:i') ?? '--:--' }}
                ~
                {{ optional($attendance?->break_end)->format('H:i') ?? '--:--' }}
            </span>
        </div>
        <div class="row">
            <span class="label">当日の勤務時間</span>
            <span class="value">
                @if (!is_null($workMinutes))
                    {{ intdiv($workMinutes, 60) }}時間{{ $workMinutes % 60 }}分
                @else
                    --
                @endif
            </span>
        </div>
     </div>

     <!-- 打刻ボタン(状態に応じて押せないものは無効化) -->
      @php
        $isWorking = $status === '勤務中';
        $isBreak = $status === '休憩中';
        $notIn = $status === '未出勤';
      @endphp 
      <div class="grid">
        <form action="{{ route('attendance.punch') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="check_in">
            <button class="punch punch-in" {{ $notIn ? '' : 'disabled' }}>出勤</button>
        </form>

        <form action="{{ route('attendance.punch') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="check_out">
            <button class="punch punch-out" {{ $isWorking ? '' : 'disabled' }}>退勤</button>
        </form>

        <form action="{{ route('attendance.punch') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="break_start">
            <button class="punch punch-bstart" {{ $isWorking ? '' : 'disabled' }}>休憩開始</button>
        </form>
        
        <form action="{{ route('attendance.punch') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="break_end">
            <button class="punch punch-bend" {{ $isBreak ? '' : 'disabled' }}>休憩終了</button>
        </form>
      </div>
</div>
<script>
    // 現在時刻を1秒ごとに更新
    setInterval(() => {
        const n = new Date();
        const p = x => String(x).padStart(2, '0');
        document.getElementById('clock').textContent =
            p(n.getHours()) + ':' + p(n.getMinutes()) + ':' + p(n.getSeconds());
    }, 1000);
</script>
</body>
</html>