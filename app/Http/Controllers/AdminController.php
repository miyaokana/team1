<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 共通チェック
    private function checkAdmin()
    {
        if (!auth()->check() || auth()->user()->role != 1) {
            abort(403);
        }
    }

    public function index()
    {
        $this->checkAdmin();

        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function create()
    {
        $this->checkAdmin();
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        // ここにバリデーションを追加します
        $request->validate([
            'user_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', 'min:8'], // 最低8文字以上
            'role'      => ['required', 'in:0,1'],          // 0（一般）か 1（管理者）のみ許可
        ], [
            // 必要に応じて日本語のエラーメッセージをカスタムできます（任意）
            'user_name.required' => '名前は必須項目です。',
            'email.required'     => 'メールアドレスは必須項目です。',
            'email.email'        => '正しいメールアドレスの形式で入力してください。',
            'email.unique'       => 'このメールアドレスは既に登録されています。',
            'password.required'  => 'パスワードは必須項目です。',
            'password.min'       => 'パスワードは8文字以上で入力してください。',
        ]);

        User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password), // ✅ 修正
            'user_name' => $request->user_name,
            'role' => $request->role ?? 0
        ]);

        return redirect('/admin/users');
    }

    // 一括登録画面の表示
    public function showUploadForm()
    {
        $this->checkAdmin();
        return view('admin.upload');
    }

    // CSVファイルのインポート処理
    public function import(Request $request)
    {
        $this->checkAdmin();

        // 1. ファイルのバリデーション（CSV形式、最大2MBまでなど）
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'csv_file.required' => 'CSVファイルを選択してください。',
            'csv_file.mimes'    => 'ファイル形式はCSVのみ対応しています。',
        ]);

        // 2. アップロードされたファイルを取得
        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        // 3. ファイルを開く
        $fp = fopen($path, 'r');
        
        // 1行目（ヘッダー：名前,Email,パスワード,権限 などの行）をスキップする場合
        fgetcsv($fp); 

        // 大量登録でエラーが起きた場合に、全てを取り消せるよう「トランザクション」を使用
        DB::beginTransaction();

        try {
            // 4. CSVを1行ずつ読み込んで処理
            while (($row = fgetcsv($fp)) !== FALSE) {
                // 文字化け対策（SJISからUTF-8へ変換が必要な場合のみコメントアウトを解除）
                // mb_convert_variables('UTF-8', 'SJIS-win', $row);

                // CSVの列の並び順の想定: 
                // $row[0] = 名前, $row[1] = Email, $row[2] = パスワード, $row[3] = 権限
                
                // 行が空、または必要なデータが足りない場合はスキップ
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    continue;
                }

                // すでに同じEmailが存在する場合はスキップ（またはエラーにする）
                if (User::where('email', $row[1])->exists()) {
                    continue;
                }

                User::create([
                    'user_name' => $row[0],
                    'email'     => $row[1],
                    'password'  => Hash::make($row[2]), // パスワードをハッシュ化
                    'role'      => isset($row[3]) ? (int)$row[3] : 0, // 未指定なら0（一般）
                ]);
            }

            fclose($fp);
            DB::commit(); // すべて成功したら確定

            return redirect('/admin/users')->with('success', 'ユーザーの一括登録が完了しました。');

        } catch (\Exception $e) {
            fclose($fp);
            DB::rollBack(); // 途中でエラーが起きたらすべて巻き戻す
            
            return back()->withErrors(['csv_file' => 'CSVの解析中にエラーが発生しました。データを確認してください。']);
        }
    }

    public function storeMultiple(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'users' => ['required', 'array', 'min:1'],
            'users.*.user_name' => ['required', 'string', 'max:255'],
            'users.*.email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'users.*.password'  => ['required', 'string', 'min:8'],
            'users.*.role'      => ['required', 'in:0,1'],
        ], [
            'users.*.user_name.required' => '名前は必須項目です。',
            'users.*.email.required'     => 'メールアドレスは必須項目です。',
            'users.*.email.email'        => '正しいメールアドレスの形式で入力してください。',
            'users.*.email.unique'       => 'このメールアドレスは既に登録されています。',
            'users.*.password.required'  => 'パスワードは必須項目です。',
            'users.*.password.min'       => 'パスワードは8文字以上で入力してください。',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->users as $userData) {
                User::create([
                    'user_name' => $userData['user_name'],
                    'email'     => $userData['email'],
                    'password'  => Hash::make($userData['password']),
                    'role'      => $userData['role'] ?? 0,
                ]);
            }
            DB::commit();
            return redirect('/admin/users')->with('success', '複数のユーザーを登録しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['users' => '登録中にエラーが発生しました。']);
        }
    }

    public function delete($id)
    {
        $this->checkAdmin();

        User::findOrFail($id)->delete();
        return redirect('/admin/users');
    }

    public function edit($id)
    {
        $this->checkAdmin();

        $user = User::findOrFail($id);
        return view('admin.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $user = User::findOrFail($id);

        $user->update([
            'email' => $request->email,
            'user_name' => $request->user_name,
            'role' => $request->role
        ]);

        return redirect('/admin/users');
    }
}
