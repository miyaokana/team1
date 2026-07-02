<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お知らせ一覧</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

<div class="wrap">

    <h2>お知らせ一覧</h2>

    <div class="history">

        @forelse($notices as $notice)

            <div class="history-item">
                <span class="tag">システム通知</span>
                {{ $notice->title }}
            </div>

        @empty

            <div class="history-item">
                通知はありません
            </div>

        @endforelse

    </div>

    <br>

    <a href="{{ route('dashboard') }}">
        ← ダッシュボードに戻る
    </a>

</div>

</body>
</html>