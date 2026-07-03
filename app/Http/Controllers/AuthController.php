<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // ✅ これ追加
use Laravel\Socialite\Facades\Socialite;


class AuthController extends Controller
{
    // 登録画面
    public function showRegister()
    {
        return view('register');
    }

    

    // ✅ 登録処理（ここ修正）
    public function register(Request $request)
    {
        User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password), // ✅ 重要
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

    // ✅ ログイン処理
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            // ✅ セッション再生成（セキュリティ）
            $request->session()->regenerate();

            // 管理者
            if (auth()->user()->role == 1) {
                return redirect('/admin/users');
            }

            // 一般ユーザ
            return redirect('/dashboard');
        }

        return back()->with('error', 'メールアドレスまたはパスワードが違います');
    }

    // ログアウト
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
    public function handleGoogleCallback()
{
    $googleUser = Socialite::driver('google')->user();

    $user = User::firstOrCreate(
        ['email' => $googleUser->email],
        [
            'user_name' => $googleUser->name,
            'password' => bcrypt(Str::random(20)),
        ]
    );

    Auth::login($user);

    return redirect()->route('dashboard');
    }
}