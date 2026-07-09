<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->user_name }}さんの勤怠</title>
    <link rel="stylesheet" href="{{ asset('css/admin/user_attendance.css') }}">
</head>
<body>

<div class="admin-wrapper">

    <header class="top-bar">
        <div class="brand-area">
            <h1>ギンクラ 管理画面</h1>
            <span class="company-badge">{{ $companyName }}</span>
        </div>
        <div class="nav-links">
            <a href="/admin/users" class="nav-item">← ユーザ一覧に戻る</a>
            <a href="{{ route('approvals.index') }}" class="nav-item">申請一覧・承認</a>
        </div>
    </header>

    <div class="container">

        {{-- 上部：プロフィール＋サマリー＋グラフ --}}
        <div class="profile-grid">

            {{-- 左：プロフィール（縦長） --}}
            <div class="card profile-card">
                <div class="avatar">{{ mb_substr($user->user_name, 0, 1) }}</div>
                <h2 class="profile-name">{{ $user->user_name }}</h2>
                <p class="profile-email">{{ $user->email }}</p>
                <span class="role-badge {{ $user->role == 1 ? 'admin' : 'user' }}">
                    {{ $user->role == 1 ? '管理者' : '一般' }}
                </span>
                <div class="profile-meta">
                    <span>所属</span>
                    <strong>{{ $companyName }}</strong>
                </div>
            </div>

            {{-- 右上：当月サマリー数字 --}}
            <div class="card stats-card">
                <div class="card-title">{{ $summary['monthLabel'] }} のサマリー</div>
                <div class="stats-row">
                    <div class="stat">
                        <div class="stat-num">{{ $summary['workDays'] }}</div>
                        <div class="stat-label">出勤日数</div>
                    </div>
                    <div class="stat">
                        <div class="stat-num">{{ $summary['workHours'] }}<span class="unit">時間</span>{{ $summary['workMins'] }}<span class="unit">分</span></div>
                        <div class="stat-label">実働時間</div>
                    </div>
                </div>
            </div>

            {{-- 右下：遅刻・早退・欠勤グラフ --}}
            @php
                $maxCount = max($summary['late'], $summary['early'], $summary['absence'], 1);
            @endphp
            <div class="card graph-card">
                <div class="card-title">{{ $summary['monthLabel'] }} の勤怠状況（承認済み）</div>
                <div class="bar-graph">
                    <div class="bar-item">
                        <div class="bar-wrap">
                            <div class="bar bar-late" style="height: {{ $summary['late'] / $maxCount * 100 }}%"></div>
                        </div>
                        <div class="bar-count">{{ $summary['late'] }}</div>
                        <div class="bar-label">遅刻</div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-wrap">
                            <div class="bar bar-early" style="height: {{ $summary['early'] / $maxCount * 100 }}%"></div>
                        </div>
                        <div class="bar-count">{{ $summary['early'] }}</div>
                        <div class="bar-label">早退</div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-wrap">
                            <div class="bar bar-absence" style="height: {{ $summary['absence'] / $maxCount * 100 }}%"></div>
                        </div>
                        <div class="bar-count">{{ $summary['absence'] }}</div>
                        <div class="bar-label">欠勤</div>
                    </div>
                </div>
            </div>

        </div>

        {{-- 下部：全履歴テーブル（横いっぱい） --}}
        <div class="card table-card">
            <div class="card-title">勤怠履歴（全期間）</div>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>実働</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $a)
                        @php
                            $mins = null;
                            if ($a->check_in && $a->check_out) {
                                $mins = (int) abs(\Carbon\Carbon::parse($a->check_in)->diffInMinutes(\Carbon\Carbon::parse($a->check_out)));
                                if ($a->break_start && $a->break_end) {
                                    $mins -= (int) abs(\Carbon\Carbon::parse($a->break_start)->diffInMinutes(\Carbon\Carbon::parse($a->break_end)));
                                }
                                $mins = max(0, $mins);
                            }
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($a->work_date)->format('n/j') }}</td>
                            <td>{{ optional($a->check_in)->format('H:i') ?? '--:--' }}</td>
                            <td>{{ optional($a->check_out)->format('H:i') ?? '--:--' }}</td>
                            <td>{{ optional($a->break_start)->format('H:i') ?? '--:--' }}〜{{ optional($a->break_end)->format('H:i') ?? '--:--' }}</td>
                            <td>{{ !is_null($mins) ? intdiv($mins, 60).'時間'.($mins % 60).'分' : '--' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">勤怠記録がありません。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>