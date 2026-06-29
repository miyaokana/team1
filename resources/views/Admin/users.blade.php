<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザ一覧</title>
</head>
<body>

<h2>ユーザ一覧</h2>

<p>
    <a href="/admin/users/create">ユーザ追加</a> |
    <a href="/dashboard">ダッシュボード</a>
</p>

<table border="1" cellpadding="5">
<tr>
    <th>ID</th>
    <th>名前</th>
    <th>Email</th>
    <th>権限</th>
    <th>操作</th>
</tr>

@foreach($users as $user)
<tr>
    <td>{{ $user->id }}</td>
    <td>{{ $user->user_name }}</td>
    <td>{{ $user->email }}</td>
    <td>{{ $user->role }}</td>
    <td>
        <a href="/admin/users/edit/{{ $user->id }}">編集</a>
        <a href="/admin/users/delete/{{ $user->id }}" onclick="return confirm('削除しますか？')">削除</a>
    </td>
</tr>
@endforeach

</table>

</body>
</html>