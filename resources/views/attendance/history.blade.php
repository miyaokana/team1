<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>打刻履歴</title>
</head>
<body>
    <div class="layout">
        @include('layouts.sidebar')

        <div class="wrap">
            <h1>打刻履歴</h1>
            <p class="history-note">直近30日分を表示しています。</p>

            @if ($rows->isEmpty())
                <p>まだ打刻記録がありません。</p>
            @else
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>出勤</th>
                            <th>退勤</th>
                            <th>休憩</th>
                            <th>勤務時間</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php
                                $a = $row['record'];
                                $wd = ['日', '月', '火', '水', '木', '金', '土'][$a->work_date->dayOfWeek];
                            @endphp
                            <tr>
                                <td>{{ $a->work_date->format('n/j') }} ({{ $wd }}) </td>
                                <td>{{ optional($a->check_in)->format('H:i') ?? '--:--' }}</td>
                                <td>{{ optional($a->check_out)->format('H:i') ?? '--:--' }}</td>
                                <td>
                                    {{ optional($a->break_start)->format('H:i') ?? '--:--' }}
                                    ~
                                    {{ optional($a->break_end)->format('H:i') ?? '--:--' }}
                                </td>
                                <td>
                                    @if (!is_null($row['workMinutes']))
                                        {{ intdiv($row['workMinutes'], 60) }}時間{{ $row['workMinutes'] % 60 }}分
                                    @else
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
</body>

</html>