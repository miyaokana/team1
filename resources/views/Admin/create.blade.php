<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザ作成</title>
</head>
<body>

<h2>ユーザ作成</h2>

<form method="POST" action="/admin/users/store">
    @csrf

    <p>
        名前：<br>
        <input type="text" name="user_name">
    </p>

    <p>
        Email：<br>
        <input type="email" name="email">
    </p>

    <p>
        パスワード：<br>
        <input type="password" name="password">
    </p>

    <p>
        権限：<br>
        <select name="role">
            <option value="0">一般</option>
            <option value="1">管理者</option>
        </select>
    </p>

    <button type="submit">登録</button>
</form>

<p><a href="/admin/users">戻る</a></p>

</body>
</html>
