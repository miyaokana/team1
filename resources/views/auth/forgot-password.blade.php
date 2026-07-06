<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>パスワード再設定</title>
</head>
<body>

    <div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc;">
        
        @if (session('status'))
            <div style="color: green; margin-bottom: 15px; font-weight: bold;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf

            <h2>パスワード再設定</h2>

            <div style="margin-bottom: 15px;">
                <input type="email"
                       name="email"
                       placeholder="登録メールアドレス"
                       value="{{ old('email') }}"
                       style="width: 100%; padding: 8px;"
                       required>
            </div>

            <button type="submit" style="padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer;">
                再設定メール送信
            </button>
        </form>
    </div>

</body>
</html>