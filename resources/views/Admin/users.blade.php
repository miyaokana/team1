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
            <div class="brand-area">
                <h1>ギンクラ 管理画面</h1>
                <span class="company-badge">{{ $companyName }}</span>
            </div>
            <div class="nav-links">
                <a href="{{ route('approvals.index') }}" class="nav-item">申請一覧・承認</a>
                <a href="{{ route('login') }}" class="nav-item back-btn">ログイン画面へ戻る</a>
            </div>
        </header>

        <div class="container">

            <div class="header-area">
                <h2>ユーザ一覧</h2>
                <a href="/admin/users/create" class="add-btn">＋ ユーザ追加</a>
            </div>

            <div class="search-box">
                <form action="/admin/users" method="GET">

                    <div class="form-group">
                        <label for="search-name">名前:</label>
                        <input type="text" id="search-name" name="name" value="{{ request('name') }}" placeholder="名前で検索">
                    </div>

                    <div class="form-group">
                        <label for="search-email">Email:</label>
                        <input type="text" id="search-email" name="email" value="{{ request('email') }}" placeholder="Emailで検索">
                    </div>

                    <div class="form-group">
                        <label for="search-role">権限:</label>
                        <select id="search-role" name="role">
                            <option value="">すべて</option>
                            <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>管理者</option>
                            <option value="0" {{ request('role') === '0' || request('role') == '0' ? 'selected' : '' }}>一般</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="search-btn">検索</button>
                        <a href="/admin/users" class="clear-btn">クリア</a>
                    </div>

                </form>
            </div>

            <div class="table-responsive">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名前</th>
                            <th>Email</th>
                            <th>権限</th>
                            <th class="text-center">操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td><span class="id-badge">{{ $user->id }}</span></td>

                            <td>
                                <a href="/admin/users/{{ $user->id }}/attendance" class="user-link">
                                    {{ $user->user_name }}
                                </a>
                            </td>
                            <td class="email-text">{{ $user->email }}</td>
                            <td>
                                @if($user->role == 1)
                                <span class="role admin">管理者</span>
                                @else
                                <span class="role user">一般</span>
                                @endif
                            </td>
                            <td class="actions text-center">
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

    </div>

</body>

</html>