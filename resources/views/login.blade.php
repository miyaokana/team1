<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ログイン</title>
<link rel="stylesheet" href="css/form.css">
</head>

<body>

<div class="bg-wrapper">

    <!-- 図形 -->
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

        <!-- ✅ フォーム正しく -->
        <form action="/login" method="POST">
        @csrf

        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" placeholder="メールアドレスを入力">
        </div>

        <div class="form-group">
            <label>パスワード</label>
            <div class="password-box">
                <input type="password" name="password" placeholder="パスワードを入力">
                <span class="eye">👁</span>
            </div>
        </div>

        <label class="checkbox">
            <input type="checkbox">
            <span class="checkmark"></span>
            メールアドレスを保存する
        </label>

        <button type="submit">ログイン</button>

        <a href="#" class="link">パスワードを忘れた場合はこちら</a>

        <div class="google-btn">
            <img src="https://developers.google.com/identity/images/g-logo.png">
            <span>Sign in with Google</span>
        </div>

        <a href="#" class="link">アカウント作成はこちら</a>

    </form>

    </div>

    <footer class="footer">
        © 2026 ギンクラ
    </footer>

</div>

</body>
</html>