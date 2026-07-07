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

    // ★名前とEmailの個別検索に対応したindexメソッド
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

        // 最終的なリザルト（結果）をゲット
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

        return view('admin.attendance', compact('user', 'attendances'));
    }
}