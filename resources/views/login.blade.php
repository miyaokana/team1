@include('layouts.header')

<!-- フォーム専用CSS -->
<link rel="stylesheet" href="{{ asset('css/form.css') }}">

<div class="container">

    <h2>ログイン</h2>

    @if(session('error'))
        <p style="color:red;">
            {{ session('error') }}
        </p>
    @endif

    <form action="/login" method="POST">
        @csrf

        <p>
            メール：<br>
            <input type="email" name="email" placeholder="メール">
        </p>

        <p>
            パスワード：<br>
            <input type="password" name="password" placeholder="パスワード">
        </p>

        <button type="submit">ログイン</button>
    </form>

    <p>
        <a href="/register" class="btn">
            新規登録はこちら
        </a>
    </p>

</div>

@include('layouts.footer')