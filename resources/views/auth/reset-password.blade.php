<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新しいパスワードの設定</title>
    <link rel="stylesheet" href="/css/reset-password.css">
</head>
<body>

    <div class="embers">
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember ember--square"></span>
        <span class="ember"></span>
        <span class="ember ember--triangle"></span>
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember ember--square"></span>
        <span class="ember"></span>
        <span class="ember ember--triangle"></span>
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember ember--square"></span>
        <span class="ember"></span>
    </div>

    <div class="reset-card">
        <h2 class="reset-card__title">新しいパスワードの設定</h2>

        <!-- エラーメッセージの表示 -->
        @if ($errors->any())
            <div class="form-error-message">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <!-- 誰のアドレスかを隠しフィールドでポストする -->
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <label class="form-label">新しいパスワード（8文字以上）</label>
                <input type="password" name="password" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">新しいパスワード（確認用）</label>
                <!-- 「_confirmation」をつけることでLaravelのconfirmedバリデーションが自動チェックしてくれるで！ -->
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>

            <button type="submit" class="btn btn-primary">
                パスワードを変更する
            </button>
        </form>
    </div>

</body>
</html>