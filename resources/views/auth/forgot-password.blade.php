<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>パスワード再設定</title>
    <link rel="stylesheet" href="/css/forgot-password.css">
</head>
<body>

    <div class="embers">
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember ember--square"></span>
        <span class="ember"></span>
        <span class="ember ember--triangle"></span>
        <span class="ember"></span>
        <span class="ember ember--star"></span>
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

        @if (session('status'))
           <div class="success-message">
            {{ session('status') }}
        </div>
        @endif

        @if ($errors->any())
            <div class="form-error-message">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf

            <h2 class="reset-card__title">パスワード再設定</h2>

            <div class="form-group">
                <input type="email"
                       name="email"
                       placeholder="登録メールアドレス"
                       value="{{ old('email') }}"
                       class="form-input"
                       required>
            </div>

            <button type="submit" class="btn btn-primary">
                再設定メール送信
            </button>
        </form>
    </div>

</body>
</html>