<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠システム</title>

    <!-- 共通CSS -->
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
</head>
<body>

<header>
    <h1>🍨 勤怠管理システム 🍭</h1>

    @if(auth()->check())
    <nav>
        <a href="/dashboard">ダッシュボード</a>

        @if(auth()->user()->role == 1)
            <a href="/admin/users">管理画面</a>
        @endif

        <a href="/logout">ログアウト</a>
    </nav>
    @endif
</header>