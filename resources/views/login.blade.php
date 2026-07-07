<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ログイン</title>
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
</head>

<body>

<div class="bg-wrapper">

    <!--  図形  -->
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>
    <div class="bg-shape shape3"></div>
    <div class="bg-shape shape4"></div>

    <div class="hex hex1"></div>
    <div class="hex hex2"></div>

    <div class="container">

        <div class="logo-area">
            <h1>ギンクラ</h1>
            <p class="subtitle">ログイン</p>
        </div>

       @if ($errors->has('login'))
        <div class="error-box">
            {{ $errors->first('login') }}
        </div>
        @endif

        <!-- ✅ 成功メッセージ -->
        @if (session('status'))
        <div class="success-box">
            {{ session('status') }}
        </div>
        @endif

        <!-- ✅ フォーム（修正済み） -->
        <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="メールアドレスを入力">
        </div>

        <div class="form-group">
            <label>パスワード</label>
        <div class="password-box">
            <input
                type="password"
                id="password"
                name="password"
                placeholder="パスワードを入力">

            <span class="eye" id="togglePassword">👁</span>
        </div>
        </div>

        <label class="checkbox">
            <input type="checkbox">
            <span class="checkmark"></span>
            メールアドレスを保存する
        </label>

        <button type="submit">ログイン</button>

        <!-- ✅ パスワード忘れ -->
        <a href="{{ route('password.request') }}" class="link">
            パスワードを忘れた場合はこちら
        </a>

        <!-- ✅ Google -->
        <a href="{{ route('google.login') }}" class="google-btn">
            <img src="https://developers.google.com/identity/images/g-logo.png">
            <span>Sign in with Google</span>
        </a>
        

        </form>

    </div>

    <footer class="footer">
        © 2026 ギンクラ
    </footer>

</div>
<script>
document.getElementById('togglePassword').addEventListener('click', function () {

    const password = document.getElementById('password');

    if (password.type === 'password') {
        password.type = 'text';
        this.textContent = '🙈';
    } else {
        password.type = 'password';
        this.textContent = '👁';
    }
});
</script>


</body>
</html>