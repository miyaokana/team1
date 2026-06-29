@include('layouts.header')

<!-- ページ専用CSS -->
<link rel="stylesheet" href="{{ asset('css/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/form.css') }}">

<div class="container">

    <h2>ログイン成功</h2>

    <p>
        ようこそ {{ auth()->user()->user_name }} さん
    </p>

    <p>
        <a href="/dashboard" class="btn edit">ダッシュボード</a>
    </p>

    @if(auth()->user()->role == 1)
    <p>
        <a href="/admin/users" class="btn edit">管理画面へ</a>
    </p>
    @endif

    <p>
        <a href="/logout" class="btn delete">ログアウト</a>
    </p>

</div>

@include('layouts.footer')