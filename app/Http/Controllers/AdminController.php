<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    // ✅ ユーザ一覧
    public function index()
    {
        // 管理者だけ許可
        if (!auth()->check() || auth()->user()->role != 1) {
            abort(403);
        }

        $users = User::all();

        return view('admin.users', compact('users'));
    }

    // ✅ 新規作成画面
    public function create()
    {
        return view('admin.create');
    }

    // ✅ 登録処理
    public function store(Request $request)
    {
        User::create([
            'email' => $request->email,
            'password' => $request->password,
            'user_name' => $request->user_name,
            'role' => $request->role ?? 0
        ]);

        return redirect('/admin/users');
    }

    // ✅ 削除
    public function delete($id)
    {
        User::find($id)->delete();

        return redirect('/admin/users');
    }

        // 編集画面
    public function edit($id)
    {
        $user = User::find($id);
        return view('admin.edit', compact('user'));
    }

    // 更新処理
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        $user->update([
            'email' => $request->email,
            'user_name' => $request->user_name,
            'role' => $request->role
        ]);

        return redirect('/admin/users');
    }

}
