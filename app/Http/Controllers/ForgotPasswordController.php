<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /**
     * パスワード再設定画面を表示する
     */
    public function showForm()
    {
        // 修正：authフォルダの中にあるので ドット「.」で繋ぐんや！
        return view('auth.forgot-password'); 
    }

    /**
     * パスワード再設定メールを送信する（DB変更なし）
     */
    public function sendResetLink(Request $request)
    {
        // 1. 入力値のバリデーション
        $request->validate([
            'email' => 'required|email',
        ]);

        // 2. DBはノータッチ！既存のusersテーブルにメールアドレスが存在するかチェック
        $user = User::where('email', $request->email)->first();

        // 3. ユーザーが存在する場合のみ、Gmail経由でメールをデリバリー
        if ($user) {
            Mail::raw(
                "こんにちは、{$user->user_name}さん。\n\n" .
                "パスワード再設定のリクエストを受け付けました。\n" .
                "（※このメールはDBを変更しない、研修用の簡易送信テストです）", 
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('【研修テスト】パスワード再設定メール');
                }
            );
        }

        // 4. セキュリティの定石通り、成否に関わらず同じサクセスメッセージをリターン
        return back()->with('status', '再設定メールを送信しました');
    }
}