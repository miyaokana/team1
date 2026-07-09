<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザ編集</title>
    <link rel="stylesheet" href="{{ asset('css/users-edit.css') }}">
</head>
<body class="edit-user-page">

<div class="page-wrapper">

    <div class="page-header">
        <h2 class="page-title">ユーザ編集</h2>
    </div>

    <form method="POST" action="/admin/users/update/{{ $user->id }}" class="form-card">
        @csrf

        <div class="form-group">
            <label>名前</label>
            <input type="text" name="user_name" value="{{ $user->user_name }}">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ $user->email }}">
        </div>

        <div class="form-group">
            <label>権限</label>
            <select name="role">
                <option value="0" {{ $user->role == 0 ? 'selected' : '' }}>一般</option>
                <option value="1" {{ $user->role == 1 ? 'selected' : '' }}>管理者</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">更新</button>
        </div>
    </form>

    <p class="back-link"><a href="/admin/users">← ユーザー一覧に戻る</a></p>

</div>

</body>
</html>