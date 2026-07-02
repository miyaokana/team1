<form action="#" method="POST">
    @csrf

    <h2>パスワード再設定</h2>

    <input type="email"
           name="email"
           placeholder="登録メールアドレス">

    <button type="submit">
        再設定メール送信
    </button>
</form>