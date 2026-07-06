<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Hash;



class PasswordResetController extends Controller

{

    /**

     * 1. メールアドレス入力画面を表示

     */

    public function showForm()

    {

        return view('auth.forgot-password');

    }



    /**

     * 2. パスワード再設定用のURLを含んだ【HTMLメール】を送信（ビュー不要・確実版）

     */

    public function sendResetLink(Request $request)

    {

        $request->validate([

            'email' => 'required|email',

        ]);



        // 既存のusersテーブルから対象のユーザーをセレクト

        $user = User::where('email', $request->email)->first();



        if ($user) {

            // メールアドレスをベースにした変更URLをジェネレート

            $resetUrl = route('password.reset', ['email' => $user->email]);



            // 💡 ビューを使わず、ここでダイレクトにHTMLの文章とAタグ（リンク）を組み立てる！

            $htmlContent = "

                <p>こんにちは、<strong>{$user->user_name}さん</strong>。</p>

                <p>パスワード再設定のリクエストを受け付けました。</p>

                <p>以下の青いボタンをクリックして、新しいパスワードを設定してください。</p>

                <p style='margin: 20px 0;'>

                    <a href='{$resetUrl}' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>

                        パスワードを再設定する

                    </a>

                </p>

                <p>※ボタンが押せない場合は、以下のURLをブラウザにコピペしてください。</p>

                <p><a href='{$resetUrl}'>{$resetUrl}</a></p>

                <hr style='border: none; border-top: 1px solid #ccc; margin: 20px 0;'>

                <p style='font-size: 12px; color: #666;'>（※新しいコントローラーから確実に送信されたテストURLです）</p>

            ";



            // 💡 Mail::html を使うことで、ビューファイル無しでHTMLメールを100%送信するで！

            Mail::html($htmlContent, function ($message) use ($user) {

                $message->to($user->email)

                        ->subject('【研修テスト】パスワード再設定URLのお知らせ');

            });

        }



        return back()->with('status', '再設定用URLを送信しました');

    }



    /**

     * 3. メールのURLから遷移してきたパスワード入力画面を表示

     */

    public function showResetForm($email)

    {

        // URLに含まれるemailをそのままViewにパスする

        return view('auth.reset-password', ['email' => $email]);

    }



    /**

     * 4. 新しいパスワードをDBのusersテーブルにアップデート

     */

    public function updatePassword(Request $request)

    {

        // 入力バリデーション（パスワードの確認一致チェック含む）

        $request->validate([

            'email' => 'required|email',

            'password' => 'required|string|min:8|confirmed', // password_confirmationと一致しているか

        ]);



        // 既存のusersテーブルから対象のユーザーをセレクト

        $user = User::where('email', $request->email)->first();



        if (!$user) {

            return redirect()->route('login')->withErrors(['email' => 'ユーザーが見つかりませんでした。']);

        }



        // DBの構造は変えず、passwordカラムのデータだけを新しいハッシュ値にアップデート！

        $user->password = Hash::make($request->password);

        $user->save();



        // ログイン画面へリダイレクトして、サクセスメッセージを表示

        return redirect()->route('login')->with('status', 'パスワードの変更が完了しました');

    }

}