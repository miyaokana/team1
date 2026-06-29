<h2>ログイン</h2>

@if(session('error'))
    <p style="color:red;">{{ session('error') }}</p>
@endif

<form action="/login" method="POST">
    @csrf

    <input type="email" name="email" placeholder="メール"><br>
    <input type="password" name="password" placeholder="パスワード"><br>

    <button type="submit">ログイン</button>
</form>

<a href="/register">新規登録はこちら</a>