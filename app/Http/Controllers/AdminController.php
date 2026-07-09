<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Attendance;
use Carbon\Constants\Format;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 共通チェック
    private function checkAdmin()
    {
        if (!auth()->check() || auth()->user()->role != 1) {
            abort(403);
        }
    }

    // 自社ユーザだけに絞る
    public function findCompanyUser($id)
    {
        return User::where('company_id', auth()->user()->company_id)
            ->findOrFail($id);
    }

    // 名前とEmailの個別検索に対応したindexメソッド
    public function index(Request $request)
    {
        $this->checkAdmin();

        // クエリビルダを始動 自社ユーザだけに絞る
        $query = User::where('company_id', auth()->user()->company_id);

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

        $today = now()->toDateString();

        // 各ユーザに今日の勤務状態を付与
        foreach ($users as $u) {
            $shift = \App\Models\Shift::where('user_id', $u->id)
                ->where('shift_date', $today)
                ->first();

            $attendance = \App\Models\Attendance::where('user_id', $u->id)
                ->where('work_date', $today)
                ->first();

            $u->today_state = $this->resolveTodayState($shift, $attendance);
        }

        // 状態別カウント(絞り込み前の全体で数える)
        $counts = [
            'normal' => $users->where('today_state', 'normal')->count(),
            'late'   => $users->where('today_state', 'late')->count(),
            'absent'   => $users->where('today_state', 'absent')->count(),
            'before'   => $users->where('today_state', 'before')->count(),
            'off'   => $users->where('today_state', 'off')->count(),
            'total'   => $users->count(),
        ];

        // 状態フィルタ(サマリーカードのクリックで絞る)
        $activeState = $request->input('state');
        if ($activeState && in_array($activeState, ['normal', 'late', 'before', 'off'], true)) {
            $users = $users->where('today_state', $activeState)->values();
        }

        // ログイン中の管理者の会社名を取得
        $companyName = auth()->user()->company->name;

        return view('admin.users', compact('users', 'companyName', 'counts', 'activeState'));

    }

    public function create()
    {
        $this->checkAdmin();
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $companyId = auth()->user()->company_id;

        // ここにバリデーションを追加します
        $request->validate([
            'user_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', 'min:8'], // 最低8文字以上
            'role'      => ['required', 'in:0,1'],          // 0（一般）か 1（管理者）のみ許可
        ], [
            // 必要に応じて日本語のエラーメッセージをカスタムできます（任意）
            'user_name.required' => '名前は必須項目です。',
            'email.required'     => 'メールアドレスは必須項目です。',
            'email.email'        => '正しいメールアドレスの形式で入力してください。',
            'email.unique'       => 'このメールアドレスは既に登録されています。',
            'password.required'  => 'パスワードは必須項目です。',
            'password.min'       => 'パスワードは8文字以上で入力してください。',
        ]);

        User::create([
            'company_id' => $companyId,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_name' => $request->user_name,
            'role' => $request->role ?? 0
        ]);

        return redirect('/admin/users');
    }

    // 一括登録画面の表示
    public function showUploadForm()
    {
        $this->checkAdmin();
        return view('admin.upload');
    }

    // CSVファイルのインポート処理
    public function import(Request $request)
    {
        $this->checkAdmin();

        $companyId = auth()->user()->company_id;

        // 1. ファイルのバリデーション（CSV形式、最大2MBまでなど）
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'csv_file.required' => 'CSVファイルを選択してください。',
            'csv_file.mimes'    => 'ファイル形式はCSVのみ対応しています。',
        ]);

        // 2. アップロードされたファイルを取得
        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        // 3. ファイルを開く
        $fp = fopen($path, 'r');

        // 1行目（ヘッダー：名前,Email,パスワード,権限 などの行）をスキップする場合
        fgetcsv($fp);

        // 大量登録でエラーが起きた場合に、全てを取り消せるよう「トランザクション」を使用
        DB::beginTransaction();

        $successCount = 0;
        $skipCount = 0;

        try {
            // 4. CSVを1行ずつ読み込んで処理
            while (($row = fgetcsv($fp)) !== FALSE) {
                // 文字化け対策（SJISからUTF-8へ変換が必要な場合のみコメントアウトを解除）
                // mb_convert_variables('UTF-8', 'SJIS-win', $row);

                // CSVの列の並び順の想定: 
                // $row[0] = 名前, $row[1] = Email, $row[2] = パスワード, $row[3] = 権限

                // 行が空、または必要なデータが足りない場合はスキップ
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    continue;
                }

                // すでに同じEmailが存在する場合はスキップ（またはエラーにする）
                if (User::where('email', $row[1])->exists()) {
                    $skipCount++;
                    continue;
                }

                User::create([
                    'company_id' => $companyId, // 自社に紐づけ
                    'user_name' => $row[0],
                    'email'     => $row[1],
                    'password'  => Hash::make($row[2]), // パスワードをハッシュ化
                    'role'      => isset($row[3]) ? (int)$row[3] : 0, // 未指定なら0（一般）
                ]);
                $successCount++;
            }

            fclose($fp);
            DB::commit(); // すべて成功したら確定

            return redirect('/admin/users')->with(
                'success',
                "{$successCount}件登録しました。（重複 {$skipCount}件）"
            );
        } catch (\Exception $e) {
            fclose($fp);
            DB::rollBack(); // 途中でエラーが起きたらすべて巻き戻す

            return back()->withErrors(['csv_file' => 'CSVの解析中にエラーが発生しました。データを確認してください。']);
        }
    }

    public function storeMultiple(Request $request)
    {
        $this->checkAdmin();

        $companyId = auth()->user()->company_id;

        $request->validate([
            'users' => ['required', 'array', 'min:1'],
            'users.*.user_name' => ['required', 'string', 'max:255'],
            'users.*.email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'users.*.password'  => ['required', 'string', 'min:8'],
            'users.*.role'      => ['required', 'in:0,1'],
        ], [
            'users.*.user_name.required' => '名前は必須項目です。',
            'users.*.email.required'     => 'メールアドレスは必須項目です。',
            'users.*.email.email'        => '正しいメールアドレスの形式で入力してください。',
            'users.*.email.unique'       => 'このメールアドレスは既に登録されています。',
            'users.*.password.required'  => 'パスワードは必須項目です。',
            'users.*.password.min'       => 'パスワードは8文字以上で入力してください。',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->users as $userData) {
                User::create([
                    'company_id' => $companyId, // 自社に紐づけ
                    'user_name' => $userData['user_name'],
                    'email'     => $userData['email'],
                    'password'  => Hash::make($userData['password']),
                    'role'      => $userData['role'] ?? 0,
                ]);
            }
            DB::commit();
            return redirect('/admin/users')->with('success', '複数のユーザーを登録しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['users' => '登録中にエラーが発生しました。']);
        }
    }

    public function delete($id)
    {
        $this->checkAdmin();

        $user = $this->findCompanyUser($id);
        $user->delete();

        return redirect('/admin/users');
    }

    public function edit($id)
    {
        $this->checkAdmin();

        $user = $this->findCompanyUser($id);

        return view('admin.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $user = $this->findCompanyUser($id);

        $request->validate([
            'user_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', 'in:0,1'],
        ]);

        $user->update([
            'email' => $request->email,
            'user_name' => $request->user_name,
            'role' => $request->role
        ]);

        return redirect('/admin/users');
    }

    public function attendance($id)
    {
        $this->checkAdmin();

        $user = $this->findCompanyUser($id);

        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();

        // 会社名を渡す
        $companyName = auth()->user()->company->name;

        // 当月の範囲
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // 当月の勤怠だけ抽出
        $monthAttendances = $attendances->filter(function ($a) use($monthStart, $monthEnd){
            return \Carbon\Carbon::parse($a->work_date)->betweenIncluded($monthStart, $monthEnd);
        });

        // 出勤日数 (出勤打刻がある日)
        $workDays = $monthAttendances->filter(fn ($a) => $a->check_in)->count();

        // 当月の実働時間(分) = (退勤-出勤)-休憩の合計
        $workMinutes = 0;
        foreach ($monthAttendances as $a) {
            if ($a->check_in && $a->check_out) {
                $mins = (int) abs(\Carbon\Carbon::parse($a->check_in)->diffInMinutes(\Carbon\Carbon::parse($a->check_out)));
                if ($a->break_start && $a->break_end) {
                    $mins -= (int) abs(\Carbon\Carbon::parse($a->break_start)->diffInMinutes(\Carbon\Carbon::parse($a->break_end)));
                }
                $workMinutes += max(0, $mins);
            }
        }

        // 当月の承認済み申請を種別ごとに集計
        $reqCounts = \App\Models\AttendanceRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('target_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->selectRaw('type, count(*) as cnt')
            ->groupBy('type')
            ->pluck('cnt', 'type');

        $summary = [
            'monthLabel' => now()->format('Y年n月'),
            'workDays' => $workDays,
            'workHours' => intdiv($workMinutes, 60),
            'workMins' => $workMinutes % 60,
            'late' => $reqCounts['late'] ?? 0,
            'early' => $reqCounts['early_leave'] ?? 0,
            'absence' => $reqCounts['absence'] ?? 0,
        ];

        return view('Admin.attendance', compact('user', 'attendances', 'companyName', 'summary'));
    }

    // ★勤怠修正画面の表示
    public function editAttendance($id)
    {
        $this->checkAdmin();

        // 修正対象の勤怠データをゲット
        $attendance = Attendance::findOrFail($id);
        // 誰の勤怠かわかるようにユーザー情報もゲット
        $user = $this->findCompanyUser($attendance->user_id);

        // フォルダ名が大文字の「Admin」やから大文字で指定するで！
        return view('Admin.edit_attendance', compact('attendance', 'user'));
    }

    public function updateAttendance(Request $request, $id)
    {
        $this->checkAdmin();

        $attendance = Attendance::findOrFail($id);

        // 対象勤怠が自社ユーザのものか確認(他社なら404)
        $this->findCompanyUser($attendance->user_id);

        // ★画面から日付は来ないので、このデータの元々の日付（Y-m-d）をベースにするで！
        $date = \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');

        // 時刻をコンバインするセーフティ関数
        $mergeDateTime = function ($time) use ($date) {
            if (empty($time) || $time === '--:--') {
                return null;
            }
            return $date . ' ' . $time . ':00';
        };

        // 元々の日付をキープしたまま、時間だけを安全にアップデート！
        $attendance->update([
            'check_in'    => $mergeDateTime($request->check_in),
            'check_out'   => $mergeDateTime($request->check_out),
            'break_start' => $mergeDateTime($request->break_start),
            'break_end'   => $mergeDateTime($request->break_end),
        ]);

        return redirect('/admin/users/' . $attendance->user_id . '/attendance');
    }

    // 今日の勤怠状態を判定して返す
    private function resolveTodayState($shift, $attendance): string
    {
        // シフトがない -> 休み
        if (!$shift) {
            return 'off';
        }

        // シフトあり・出勤打刻あり -> 遅刻か正常か
        if ($attendance && $attendance->check_in) {
            $checkIn = \Carbon\Carbon::parse($attendance->check_in);
            $shiftStart = \Carbon\Carbon::parse($shift->start_time);
            // 分単位で比較(秒切り捨て)。開始より後なら遅刻
            return $checkIn->startOfMinute()->gt($shiftStart->startOfMinute()) ? 'late' : 'normal';
        }

        // シフトあり・未打刻・開始時間を過ぎていれば無断欠勤、まだなら出勤前
        $shiftStart = \Carbon\Carbon::parse($shift->start_time);
        return now()->gt($shiftStart) ? 'absent' : 'before';
    }
}
