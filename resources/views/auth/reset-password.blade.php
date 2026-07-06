<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新しいパスワードの設定</title>
</head>
<body>

    <div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc;">
        <h2>新しいパスワードの設定</h2>

        <!-- エラーメッセージの表示 -->
        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <!-- 誰のアドレスかを隠しフィールドでポストする -->
            <input type="hidden" name="email" value="{{ $email }}">

            <div style="margin-bottom: 15px;">
                <label>新しいパスワード（8文字以上）</label><br>
                <input type="password" name="password" style="width: 100%; padding: 8px;" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label>新しいパスワード（確認用）</label><br>
                <!-- 「_confirmation」をつけることでLaravelのconfirmedバリデーションが自動チェックしてくれるで！ -->
                <input type="password" name="password_confirmation" style="width: 100%; padding: 8px;" required>
            </div>

            <button type="submit" style="padding: 10px 15px; background-color: #28a745; color: white; border: none; cursor: pointer;">
                パスワードを変更する
            </button>
        </form>
    </div>

</body>
</html>