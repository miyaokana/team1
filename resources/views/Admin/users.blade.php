<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザ一覧</title>
    <link rel="stylesheet" href="{{ asset('css/admin/users.css') }}">
</head>
<body>

<div class="admin-wrapper">

    <header class="top-bar">
        <h1>ギンクラ 管理画面</h1>
        <a href="/admin/requests" class="nav-item">申請一覧</a>

        <a href="{{ route('login') }}" class="back-btn">
            ログイン画面へ戻る
        </a>

        <a href="{{ route('approvals.index') }}" class="nav-item">
            申請一覧・承認
        </a>
    </header>

    <div class="container">

        <div class="header-area">
            <h2>ユーザ一覧</h2>

            <a href="/admin/users/create" class="add-btn">＋ユーザ追加</a>
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>Email</th>
                    <th>権限</th>
                    <th>操作</th>
                </tr>
            </thead>

            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>
                        <a href="/admin/users/{{ $user->id }}/attendance">
                        {{ $user->user_name }}
                        </a>
                    </td>
                    <td>{{ $user->user_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->role == 1)
                            <span class="role admin">管理者</span>
                        @else
                            <span class="role user">一般</span>
                        @endif
                    </td>
                    <td class="actions">
                        <a href="/admin/users/edit/{{ $user->id }}" class="edit">編集</a>
                        <a href="/admin/users/delete/{{ $user->id }}"
                           class="delete"
                           onclick="return confirm('削除しますか？')">削除</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>

</div>

</body>
</html>