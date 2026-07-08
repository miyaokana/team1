<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>各種申請(遅刻・早退・欠勤)</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance-request.css') }}">
</head>

<body>

    @include('layouts.header')

    <div class="layout">

        @include('layouts.sidebar')

        <div class="wrap">

            <h1>各種申請(遅刻・早退・欠勤)</h1>

            <!-- 送信完了メッセージ -->
            @if (session('status'))
            <div class="flash flash-ok">{{ session('status') }}</div>
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

                <form action="{{ route('attendance_requests.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <label for="type">申請種別</label>
                        <select name="type" id="type" required>
                            <option value="late" {{ old('type') === 'late' ? 'selected' : '' }}>遅刻</option>
                            <option value="early_leave" {{ old('type') === 'early_leave' ? 'selected' : '' }}>早退</option>
                            <option value="absence" {{ old('type') === 'absence' ? 'selected' : '' }}>欠勤</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="target_date">対象日</label>
                        <input type="date" name="target_date" id="target_date"
                            value="{{ old('target_date') }}" required>
                    </div>

                    <!-- 遅刻・早退の時だけ表示する時刻覧 -->
                    <div class="form-row" id="time-row">
                        <label for="request_time">時刻</label>
                        <input type="time" name="request_time" id="request_time"
                            value="{{ old('request_time') }}">
                        <small class="form-hint">遅刻=出勤予定の時刻,早退=退勤する時刻</small>
                    </div>

                    <div class="form-row">
                        <label for="reason">理由</label>
                        <textarea name="reason" id="reason" rows="3" required>{{ old('reason') }}</textarea>
                    </div>

                    <div class="form-row">
                        <label for="attachment">添付ファイル（任意）</label>
                        <input type="file" name="attachment" id="attachment"
                            accept=".jpg,.jpeg,.png,.pdf">
                        <small class="form-hint">写真(JPG・PNG)またはPDF、5MBまで。診断書や遅延証明などがあれば添付してください。</small>
                    </div>

                    <button type="submit" class="btn-submit">申請する</button>
                </form>
            </div>

            <!-- 自分の申請一覧 -->
            <div class="card">
                <h2>申請履歴</h2>

                @if ($requests->isEmpty())
                <p>まだ申請はありません。</p>
                @else
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>対象日</th>
                            <th>種別</th>
                            <th>時刻</th>
                            <th>理由</th>
                            <th>添付</th>
                            <th>状態</th>
                            <th>申請日時</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                        <tr>
                            <td>{{ $req->target_date->format('n/j') }}</td>
                            <td>{{ \App\Models\AttendanceRequest::TYPE_LABELS[$req->type] ?? $req->type }}</td>
                            <td>{{ $req->request_time ? \Carbon\Carbon::parse($req->request_time)->format('H:i') : '-' }}</td>
                            <td>{{ $req->reason }}</td>
                            <td>
                                @if ($req->attachment_path)
                                    <a href="{{ route('attendance_requests.attachment', $req) }}">表示</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php $st = $req->status; @endphp
                                <span class="req-status req-{{ $st }}">
                                    {{ \App\Models\AttendanceRequest::STATUS_LABELS[$st] ?? $st }}
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
        // 種別に応じて時刻覧を出しわける。欠勤の時は時刻を隠す。
        const typeSelect = document.getElementById('type');
        const timeRow = document.getElementById('time-row');

        function toggleTimeRow() {
            const isAbsence = typeSelect.value === 'absence';
            timeRow.style.display = isAbsence ? 'none' : '';
        }

        typeSelect.addEventListener('change', toggleTimeRow);
        toggleTimeRow(); // 初期表示にも反映
    </script>
</body>

</html>