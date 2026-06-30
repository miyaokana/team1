<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

        User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password), // ✅ 修正
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
}
