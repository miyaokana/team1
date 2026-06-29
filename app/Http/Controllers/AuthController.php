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

    // 登録処理（DB保存）
    public function register(Request $request)
    {
        User::create([
            'email' => $request->email,
            'password' => $request->password, // ←自動でハッシュされる
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

    // ログイン処理（DB照合）
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
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