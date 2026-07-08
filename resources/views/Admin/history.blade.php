<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>各種申請(遅刻・早退・欠勤)</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/history.css') }}">
</head>
<body>

@include('layouts.header')

<div class="layout">

    @include('layouts.sidebar')

    <div class="wrap">

        <div class="history-card">

            <h1>打刻履歴</h1>

            <p class="history-note">
                直近30日分を表示しています。
            </p>

            @if ($rows->isEmpty())

                <p>まだ打刻記録がありません。</p>

            @else

                <table class="history-table">

                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>状態</th>
                            <th>予定</th>
                            <th>出勤</th>
                            <th>退勤</th>
                            <th>休憩</th>
                            <th>勤務時間</th>
                            <th>差分</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($rows as $row)

                            @php
                                $date = $row['date'];
                                $a = $row['record'];
                                $shift = $row['shift'];
                                $wd = ['日','月','火','水','木','金','土'][$date->dayOfWeek];
                            @endphp

                            <tr>

                                <td>{{ $date->format('n/j') }} ({{ $wd }})</td>

                                <td>
                                    @if ($row['state'] === '欠勤')
                                        <span class="state-absent">欠勤</span>
                                    @else
                                        {{ $row['state'] }}
                                    @endif
                                </td>

                                <td>
                                    @if ($shift)
                                        {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                                        ～
                                        {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                    @else
                                        予定なし
                                    @endif
                                </td>

                                <td>{{ optional($a?->check_in)->format('H:i') ?? '--:--' }}</td>

                                <td>{{ optional($a?->check_out)->format('H:i') ?? '--:--' }}</td>

                                <td>
                                    {{ optional($a?->break_start)->format('H:i') ?? '--:--' }}
                                    ～
                                    {{ optional($a?->break_end)->format('H:i') ?? '--:--' }}
                                </td>

                                <td>
                                    @if (!is_null($row['workMinutes']))
                                        {{ intdiv($row['workMinutes'],60) }}時間{{ $row['workMinutes'] % 60 }}分
                                    @else
                                        --
                                    @endif
                                </td>

                                <td>
                                    @php $diff = $row['diff']; @endphp

                                    @if (!is_null($diff['late']))
                                        <span class="diff-late">遅刻{{ $diff['late'] }}分</span>
                                    @endif

                                    @if (!is_null($diff['early']))
                                        <span class="diff-early">早退{{ $diff['early'] }}分</span>
                                    @endif

                                    @if (!is_null($diff['overtime']))
                                        <span class="diff-overtime">残業{{ $diff['overtime'] }}分</span>
                                    @endif

                                    @if (
                                        is_null($diff['late']) &&
                                        is_null($diff['early']) &&
                                        is_null($diff['overtime'])
                                    )
                                        --
                                    @endif

                                </td>

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