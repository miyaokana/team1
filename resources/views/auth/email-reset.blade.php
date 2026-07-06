<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <p>こんにちは、{{ $user_name }}さん。</p>

    <p>パスワード再設定のリクエストを受け付けました。</p>
    <p>以下の一時的なURLリンクをクリックして、新しいパスワードを設定してください。</p>

    <p style="margin: 20px 0;">
        <!-- 💡 ここがハイパーリンク！青い文字でクリックして飛べるようになるで！ -->
        <a href="{{ $reset_url }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            パスワードを再設定する
        </a>
    </p>

    <p>※もし上のボタンがクリックできない場合は、以下のURLをブラウザのURLバーに直接コピペしてください。</p>
    <p><a href="{{ $reset_url }}">{{ $reset_url }}</a></p>

    <hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">
    <p style="font-size: 12px; color: #666;">（※このメールはDBを変更しない、研修用の送信テストです）</p>
</body>
</html>