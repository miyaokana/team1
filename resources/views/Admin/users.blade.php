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

        <div class="search-box" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 5px; border: 1px solid #ddd;">
            <form action="/admin/users" method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                
                <div class="form-group" style="display: flex; align-items: center;">
                    <label for="search-name" style="font-weight: bold; margin-right: 5px; white-space: nowrap;">名前:</label>
                    <input type="text" id="search-name" name="name" value="{{ request('name') }}" placeholder="名前で検索" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 160px;">
                </div>

                <div class="form-group" style="display: flex; align-items: center;">
                    <label for="search-email" style="font-weight: bold; margin-right: 5px; white-space: nowrap;">Email:</label>
                    <input type="text" id="search-email" name="email" value="{{ request('email') }}" placeholder="Emailで検索" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 180px;">
                </div>

                <div class="form-group" style="display: flex; align-items: center;">
                    <label for="search-role" style="font-weight: bold; margin-right: 5px; white-space: nowrap;">権限:</label>
                    <select id="search-role" name="role" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; min-width: 100px;">
                        <option value="">すべて</option>
                        <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>管理者</option>
                        <option value="0" {{ request('role') === '0' || request('role') == '0' ? 'selected' : '' }}>一般</option>
                    </select>
                </div>

                <div class="form-actions" style="display: flex; align-items: center;">
                    <button type="submit" class="search-btn" style="padding: 6px 15px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">検索</button>
                    <a href="/admin/users" class="clear-btn" style="padding: 6px 15px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 4px; margin-left: 5px; font-size: 14px; text-align: center;">クリア</a>
                </div>

            </form>
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
                        {{ $user->id }}
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