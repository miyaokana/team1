<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>有給・特別休暇申請</title>
</head>
<body>
    <div class="layout">
        @include('layouts.sidebar')

        <div class="wrap">
            <h1>有給・特別休暇申請</h1>

            <!-- 送信完了メッセージ -->
             @if (session('status'))
                <div class="flash flash-ok">{{ session('status') }}</div>
             @endif

            <!-- 期間・半休制約などのエラー -->
             @if (session('error'))
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

            <!-- 申請フォーム -->
            <div class="card">
                <h2>新規申請</h2>
                <form action="{{ route('leave_requests.store') }}" method="POST">
                    @csrf

                    <div class="form-row">
                        <label for="type">種別</label>
                        <select name="type" id="type" required>
                            <option value="paid" {{ old('type') === 'paid' ? 'selected' : '' }}>有給</option>
                            <option value="special" {{ old('type') === 'special' ? 'selected' : '' }}>特別休暇</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="day_type">特別区分</label>
                        <select name="day_type" id="day_type" required>
                            <option value="full_day" {{ old('day_type') === 'full_day' ? 'selected' : '' }}>全日</option>
                            <option value="am" {{ old('day_type') === 'am' ? 'selected' : '' }}>午前半休</option>     
                            <option value="pm" {{ old('day_type') === 'pm' ? 'selected' : '' }}>午後半休</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="start_date">開始日</label>
                        <input type="date" name="start_date" id="start_date"
                            value="{{ old('start_date') }}" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="end_date">終了日</label>
                        <input type="date" name="end_date" id="end_date"
                            value="{{ old('end_date') }}" required>
                        <small class="form-hint">半休(午前・午後)の場合は開始日と同じ日にしてください。</small>
                    </div>

                    <div class="form-row">
                        <label for="reason">理由</label>
                        <textarea name="reason" id="reason" rows="3" required>{{ old('reason') }}</textarea>
                    </div>

                    <button type="submit" class="btn-submit">申請する</button>
                </form>
            </div>

            <!-- 自分の有給申請一覧 -->
             <div class="card">
                <h2>申請履歴</h2>

                @if ($requests->isEmpty())
                    <p>まだ申請はありません。</p>
                @else
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>種別</th>
                                <th>期間</th>
                                <th>区分</th>
                                <th>日数</th>
                                <th>理由</th>
                                <th>状態</th>
                                <th>申請日時</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $req)
                                @php
                                    // 全日は日数を数える(両端を含む)。半休は0.5日として扱う。
                                    if (in_array($req->day_type, ['am', 'pm'], true)) {
                                        $days = '0.5';
                                    } else {
                                        $days = $req->start_date->diffInDays($req->end_date) + 1;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ \App\Models\LeaveRequest::TYPE_LABELS[$req->type] ?? $req->type }}</td>
                                    <td>
                                        {{ $req->start_date->format('n/j') }}
                                        @if (!$req->start_date->eq($req->end_date))
                                            ~ {{ $req->end_date->format('n/j') }}
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\LeaveRequest::DAY_TYPE_LABELS[$req->day_type] ?? $req->day_type }}</td>
                                    <td>{{ $days }}日</td>
                                    <td>{{ $req->reason }}</td>
                                    <td>
                                        @php $st = $req->status; @endphp
                                        <span class="req-status req-{{ $st }}">
                                            {{ \App\Models\LeaveRequest::STATUS_LABELS[$st] ?? $st }}
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

    <script>
        // 半休(午前・午後)を選んだら終了日を開始日に合わせて固定する入力候補
        const dayType = document.getElementById('day_type');
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');

        function syncEndDate() {
            const isHalf = dayType.value === 'am' || dayType.value === 'pm';
            if (isHalf) {
                endDate.value = startDate.value;
                endDate.readOnly = true;
            } else {
                endDate.readOnly = false;
            }
        }

        dayType.addEventListener('change', syncEndDate);
        startDate.addEventListener('change', syncEndDate);
        syncEndDate(); //初期表示にも反映
    </script>
</body>

</html>