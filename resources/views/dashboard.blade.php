@include('layouts.header')

<<<<<<< HEAD
<p>ようこそ  {{ auth()->user()->user_name }} さん</p>
=======
<!-- ページ専用CSS -->
<link rel="stylesheet" href="{{ asset('css/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
>>>>>>> 3a888079d7ae08c9ed8f0a5a1b75e55425bbb7c2

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