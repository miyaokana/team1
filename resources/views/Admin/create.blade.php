<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ユーザ作成</title>
    <link rel="stylesheet" href="{{ asset('css/create.css') }}">
</head>

<body>

    <div class="container">
        <h2>ユーザ作成</h2>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('single')">個別登録</button>
            <button class="tab-btn" onclick="switchTab('multiple')">手動一括登録</button>
            <button class="tab-btn" onclick="switchTab('bulk')">CSV一括登録</button>
        </div>

        <div class="form-card-wrapper">

            <div id="form-single" class="form-content active">
                <form method="POST" action="/admin/users/store" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="user_name">名前</label>
                        <input type="text" id="user_name" name="user_name" value="{{ old('user_name') }}" placeholder="山田 太郎" autocomplete="off">
                        @error('user_name') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="example@email.com" autocomplete="off">
                        @error('email') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">パスワード</label>
                        <input type="password" id="password" name="password" autocomplete="new-password">
                        @error('password') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="role">権限</label>
                        <select id="role" name="role">
                            <option value="0" {{ old('role') == '0' ? 'selected' : '' }}>一般</option>
                            <option value="1" {{ old('role') == '1' ? 'selected' : '' }}>管理者</option>
                        </select>
                        @error('role') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="btn-submit">登録する</button>
                </form>
            </div>

            <div id="form-multiple" class="form-content">
                <form method="POST" action="/admin/users/store-multiple" novalidate>
                    @if ($errors->any())
                    <div class="error-box">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @csrf

                    @php
                    $hasMultipleError = collect($errors->keys())->contains(fn($k) => str_starts_with($k, 'users'));
                    @endphp
                    @if ($hasMultipleError)
                    <div class="error-box" style="background:#fed7d7;color:#c53030;padding:10px;border-radius:6px;margin-bottom:15px;">
                        <ul style="margin:0;padding-left:20px;">
                            @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div style="margin-bottom: 25px;">
                        <button type="button" id="add-row-btn" style="width: 100%; padding: 10px; background: #edf2f7; color: #4a5568; border: 1px solid #cbd5e0; border-radius: 6px; font-weight: bold; cursor: pointer;">＋ 入力行を追加する</button>
                    </div>
                    <div id="user-rows-container">
                        <div class="user-row" style="border-bottom: 1px dashed #e2e8f0; padding-bottom: 15px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <span style="font-size: 18px; font-weight: bold; color: #4f46e5;">ユーザー 1</span>
                            </div>
                            <div class="form-group">
                                <label>名前</label>
                                <input type="text" name="users[0][user_name]" placeholder="山田 太郎" autocomplete="off" required>
                                @error('users.0.user_name') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="users[0][email]" placeholder="example@email.com" autocomplete="off" required>
                                @error('users.0.email') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label>パスワード</label>
                                <input type="password" name="users[0][password]" autocomplete="new-password" required>
                                @error('users.0.password') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label>権限</label>
                                <select name="users[0][role]">
                                    <option value="0">一般</option>
                                    <option value="1">管理者</option>
                                </select>
                                @error('users.0.role') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">まとめて登録する</button>
                </form>
            </div>

            <div id="form-bulk" class="form-content">
                <form method="POST" action="/admin/users/import" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="csv_file">CSVファイルを選択</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv">
                        @error('csv_file') <span class="error-message">{{ $message }}</span> @enderror
                    </div>
                    <div class="csv-info">
                        <p><strong>【CSVファイルの書き方】</strong></p>
                        <p class="text-red">1行目：ヘッダー（無視されます）</p>
                        <p>2行目以降：名前, メールアドレス, パスワード, 権限</p>
                        <p>（例）</p>
                        <p>鈴木一郎,suzuki@example.com,password555,0</p>
                        <p>佐藤花子,sato@example.com,password777,1</p>
                    </div>
                    <button type="submit" class="btn-submit">アップロードして一括登録</button>
                </form>
            </div>

        </div>

        <div class="back-link">
            <a href="/admin/users">← 戻る</a>
        </div>
    </div>

    <script>
        // タブ切り替えロジック
        function switchTab(type) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.form-content').forEach(content => content.classList.remove('active'));

            if (type === 'single') {
                document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
                document.getElementById('form-single').classList.add('active');
            } else if (type === 'multiple') {
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
                document.getElementById('form-multiple').classList.add('active');
            } else if (type === 'bulk') {
                document.querySelector('.tab-btn:nth-child(3)').classList.add('active');
                document.getElementById('form-bulk').classList.add('active');
            }
        }

        // 手動一括登録の行追加ロジック
        let rowCount = 1;
        document.getElementById('add-row-btn').addEventListener('click', function() {
            rowCount++;
            const container = document.getElementById('user-rows-container');
            const newRow = document.createElement('div');
            newRow.className = 'user-row';
            newRow.style = 'border-bottom: 1px dashed #e2e8f0; padding-bottom: 15px; margin-bottom: 15px;';
            newRow.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 18px; font-weight: bold; color: #4f46e5;">ユーザー ${rowCount}</span>
                <button type="button" class="remove-row-btn" style="padding: 2px 8px; background: #fed7d7; color: #c53030; border: none; border-radius: 4px; font-size: 12px; cursor: pointer;">削除</button>
            </div>
            <div class="form-group"><label>名前</label><input type="text" name="users[${rowCount - 1}][user_name]" placeholder="山田 太郎" autocomplete="off" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="users[${rowCount - 1}][email]" placeholder="example@email.com" autocomplete="off" required></div>
            <div class="form-group"><label>パスワード</label><input type="password" name="users[${rowCount - 1}][password]" autocomplete="new-password" required></div>
            <div class="form-group"><label>権限</label><select name="users[${rowCount - 1}][role]"><option value="0">一般</option><option value="1">管理者</option></select></div>
        `;
            container.appendChild(newRow);
        });

        // 行削除ロジック
        document.getElementById('user-rows-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row-btn')) {
                e.target.closest('.user-row').remove();
            }
        });

        // エラー時の自動タブ切り替え
        const hasCsvError = "{{ $errors->has('csv_file') ? 'true' : 'false' }}";
        if (hasCsvError === 'true') {
            switchTab('bulk');
        }
        
    </script>

</body>

</html>