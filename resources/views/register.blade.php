<h2>新規登録</h2>

<form action="/register" method="POST">
    @csrf

    <input type="text" name="user_name" placeholder="名前"><br>
    <input type="email" name="email" placeholder="メール"><br>
    <input type="password" name="password" placeholder="パスワード"><br>

    <button type="submit">登録</button>
</form>

<a href="/login">ログインはこちら</a>