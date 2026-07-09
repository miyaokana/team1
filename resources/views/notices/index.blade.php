<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お知らせ一覧</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">

</head>

@include('layouts.header')

<div class="layout">

@include('layouts.sidebar')

<div class="wrap">

    <h2>お知らせ一覧</h2>

    <div class="history">

    @forelse($notices as $notice)

        <div class="history-item">

            <span class="tag">
                {{ $notice->title }}
            </span>

            <div>
                {!! nl2br(e($notice->message)) !!}
            </div>

            <small>
                {{ \Carbon\Carbon::parse($notice->date)->format('Y/m/d') }}
            </small>

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
</div>

</body>
</html>    
    
    

