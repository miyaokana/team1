<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 登録画面
    public function showRegister()
    {
        return view('register');
    }

    // 登録処理
    public function register(Request $request)
    {
        User::create([
            'email' => $request->email,
            'password' => $request->password,
            'user_name' => $request->user_name,
            'role' => 0
        ]);

        return redirect('/login');
    }

    // ログイン画面
    public function showLogin()
    {
        return view('login');
    }

    // ✅ ここが重要（修正済み）
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            // 管理者なら管理画面
            if (auth()->user()->role == 1) {
                return redirect('/admin/users');
            }

            // 一般ユーザ
            return redirect('/dashboard');
        }

        return back()->with('error', 'ログイン失敗');
    }

    // ダッシュボード
    public function dashboard()
    {
        return view('dashboard');
    }

    // ログアウト
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
