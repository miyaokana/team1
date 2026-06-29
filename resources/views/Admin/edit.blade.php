<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザ編集</title>
</head>
<body>

<h2>ユーザ編集</h2>

<form method="POST" action="/admin/users/update/{{ $user->id }}">
    @csrf

    <p>
        名前：<br>
        <input type="text" name="user_name" value="{{ $user->user_name }}">
    </p>

    <p>
        Email：<br>
        <input type="email" name="email" value="{{ $user->email }}">
    </p>

    <p>
        権限：<br>
        <select name="role">
            <option value="0" {{ $user->role == 0 ? 'selected' : '' }}>一般</option>
            <option value="1" {{ $user->role == 1 ? 'selected' : '' }}>管理者</option>
        </select>
    </p>

    <button type="submit">更新</button>
</form>

<p><a href="/admin/users">戻る</a></p>

</body>
</html>