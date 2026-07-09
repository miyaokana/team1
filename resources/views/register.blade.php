<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>会社登録</title>
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>

<div class="bg-wrapper">

    <!-- 背景図形（loginと同じトーン） -->
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>
    <div class="bg-shape shape3"></div>
    <div class="bg-shape shape4"></div>

    <div class="container">

        <div class="logo-area">
            <h1>ギンクラ</h1>
            <p class="subtitle">会社を新しく登録</p>
        </div>

        <!-- バリデーションエラー -->
        @if ($errors->any())
        <div class="error-box">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>会社名</label>
                <input type="text" name="company_name"
                       value="{{ old('company_name') }}"
                       placeholder="株式会社〇〇">
            </div>

            <div class="form-group">
                <label>お名前（最初の管理者）</label>
                <input type="text" name="user_name"
                       value="{{ old('user_name') }}"
                       placeholder="山田 太郎">
            </div>

            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="example@email.com">
            </div>

            <div class="form-group">
                <label>パスワード</label>
                <div class="password-box">
                    <input type="password" id="password" name="password"
                           placeholder="8文字以上">
                    <span class="eye" id="togglePassword">👁</span>
                </div>
            </div>

            <button type="submit">この内容で会社を登録する</button>

            <a href="{{ route('login') }}" class="link">
                すでにアカウントをお持ちの方はこちら
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