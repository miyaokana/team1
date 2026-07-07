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
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min8',
        ],[
            'company_name.required' => '会社名は必須です。',
            'user_name.required' => '名前は必須です。',
            'email.required' => 'メールアドレスは必須です。',
            'email.unique' => 'このメールアドレスは既に使われています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
        ]);

        $company = \App\Models\Company::create([
            'name' => $validated['company_name'],
        ]);

        User::create([
            'company_id' => $company->id,
            'user_name' => $validated['user_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // ✅ 重要
            'role' => 1 // 会社の最初の登録者は管理者
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

        return back()->withErrors([
            'login' => 'メールアドレスまたはパスワードが違います'
        ])->onlyInput('email');
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