<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Attendance;

class AdminController extends Controller
{
    // 共通チェック
    private function checkAdmin()
    {
        if (!auth()->check() || auth()->user()->role != 1) {
            abort(403);
        }
    }

    // 名前とEmailの個別検索に対応したindexメソッド
    public function index(Request $request)
    {
        $this->checkAdmin();

        // クエリビルダを始動
        $query = User::query();

        // 1. 名前（user_name）で検索
        if ($request->filled('name')) {
            $query->where('user_name', 'like', "%{$request->input('name')}%");
        }

        // 2. Emailで検索
        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->input('email')}%");
        }

        // 3. 権限（role）で絞り込み
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // 最終的な結果をゲット
        $users = $query->get();

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

        User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'user_name' => $request->user_name,
            'role' => $request->role ?? 0
        ]);

        return redirect('/admin/users');
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

    public function attendance($id){
        $this->checkAdmin();

        $user = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $id)
            ->orderBy('work_date', 'desc')
            ->get();

        return view('Admin.attendance', compact('user', 'attendances'));
    }

    // ★勤怠修正画面の表示
    public function editAttendance($id)
    {
        $this->checkAdmin();

        // 修正対象の勤怠データをゲット
        $attendance = Attendance::findOrFail($id);
        // 誰の勤怠かわかるようにユーザー情報もゲット
        $user = User::findOrFail($attendance->user_id);

        // フォルダ名が大文字の「Admin」やから大文字で指定するで！
        return view('Admin.edit_attendance', compact('attendance', 'user'));
    }

    // ★勤怠データの更新処理
    public function updateAttendance(Request $request, $id)
    {
        $this->checkAdmin();

        $attendance = Attendance::findOrFail($id);

        // リクエストされたデータを元にDBをアップデート！
        $attendance->update([
            'work_date'   => $request->work_date,
            'check_in'    => $request->check_in,
            'check_out'   => $request->check_out,
            'break_start' => $request->break_start,
            'break_end'   => $request->break_end,
        ]);

        // 修正が終わったら、そのユーザーの勤怠一覧画面へ戻る
        return redirect('/admin/users/' . $attendance->user_id . '/attendance');
    }
}