<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>残業申請</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/overtime_requests.css') }}">
</head>
<body>
    @include('layouts.header')
    <div class="layout">
        @include('layouts.sidebar')
        <div class="wrap">
            <h1>残業申請</h1>

            <!-- 送信完了メッセージ -->
            @if (session('status'))
                <div class="flash flash-ok">{{ session('status') }}</div>
            @endif

            <!-- 開始・終了が同じ等のエラー -->
            @if(session('error'))
                <div class="flash flash-err">{{ session('error') }}</div>
            @endif

            <!-- バリデーションエラー -->
            @if ($errors->any())
                <div class="flash flash-err">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="tab-container">
                <a href="{{ route('attendance_requests.index') }}" class="tab-link">遅刻・早退・欠勤申請</a>
                
                <a href="{{ route('overtime_requests.index') }}" class="tab-link active">残業申請</a>
                
                <a href="{{ route('leave_requests.index') }}" class="tab-link">有給・特別休暇申請</a>
            </div>

            <!-- 申請フォーム -->
            <div class="card">
                <h2>新規申請</h2>
                <form action="{{ route('overtime_requests.store') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <label for="target_date">対象日</label>
                        <input type="date" name="target_date" id="target_date"
                            value="{{ old('target_date') }}" required>
                    </div>

                    <div class="form-row">
                        <label for="start_time">開始時刻</label>
                        <input type="time" name="start_time" id="start_time"
                            value="{{ old('start_time') }}" required>
                    </div>

                    <div class="form-row">
                        <div>
                            <label for="end_time">終了時刻</label>
                            <small class="form-hint">終了が開始より早い場合は翌日として扱います(例 22:00 ~ 翌1:00)</small>
                        </div>
                        <input type="time" name="end_time" id="end_time"
                            value="{{ old('end_time') }}" required>
                    </div>

                    <div class="form-row">
                        <label for="reason">理由</label>
                        <textarea name="reason" id="reason" rows="3" required>{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit" class="btn-submit">申請する</button>
                </form>
            </div>

            <!-- 自分の残業申請一覧 -->
            <div class="card">
                <h2>申請履歴</h2>

                @if ($requests->isEmpty())
                    <p>まだ申請はありません。</p>
                @else
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>対象日</th>
                                <th>開始</th>
                                <th>終了</th>
                                <th>残業時間</th>
                                <th>理由</th>
                                <th>状態</th>
                                <th>申請日時</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $req)
                                @php
                                    // 残業時間(分)を開始終了の差から計算。翌日繰り上げ済みなので正しく出る
                                    $minutes = (int) abs($req->start_at->diffInMinutes($req->end_at));
                                @endphp
                                <tr>
                                    <td>{{ $req->target_date->format('n/j') }}</td>
                                    <td>{{ $req->start_at->format('H:i') }}</td>
                                    <td>
                                        {{ $req->end_at->format('H:i') }}
                                        @if ($req->end_at->format('Y-m-d') !== $req->start_at->format('Y-m-d'))
                                            <span class="next-day">(翌日)</span>
                                        @endif
                                    </td>
                                    <td>{{ intdiv($minutes, 60) }}時間{{ $minutes % 60 }}分</td>
                                    <td>{{ $req->reason }}</td>
                                    <td>
                                        @php $st = $req->status; @endphp
                                        <span class="req-status req-{{ $st }}">
                                            {{ \App\Models\OvertimeRequest::STATUS_LABELS[$st] ?? $st }}
                                        </span>
                                    </td>
                                    <td>{{ $req->created_at->format('n/j H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</body>
</html>