<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthInput);

        // 画面で選択されたユーザーID（指定がなければ最初のユーザー）
        $selectedUserId = $request->input('user_id');
        $users = User::all();
        $selectedUser = $selectedUserId ? User::find($selectedUserId) : $users->first();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // カレンダーのグリッド配置用（1日の曜日）
        $startOfWeek = $startOfMonth->dayOfWeek; 

        $dates = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()){
            $dates[] = $date->copy();
        }

        // 選択されたユーザーの今月のシフトだけを取得
        $shifts = collect();
        if ($selectedUser) {
            $shifts = Shift::where('user_id', $selectedUser->id)
                ->whereBetween('shift_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                ->get();
        }

        // 祝日の配列を取得
        $holidays = $this->getHolidaysForMonth($currentMonth);

        // 💡 修正ポイント: compact() に 'holidays' を追加しました
        return view('shifts.shift', compact('users', 'selectedUser', 'dates', 'currentMonth', 'startOfWeek', 'shifts', 'holidays'));
    }

    /**
     * 日本の祝日（振替休日・国民の休日含む）の自動判定ロジック
     */
    private function getHolidaysForMonth(Carbon $month)
    {
        $year = $month->year;

        // ハッピーマンデー（特定週の月曜日）の計算用
        $getHappyMonday = function($year, $month, $week) {
            $firstDay = Carbon::create($year, $month, 1);
            $firstMonday = $firstDay->dayOfWeek === Carbon::MONDAY 
                ? $firstDay 
                : $firstDay->next(Carbon::MONDAY);
            return $firstMonday->addWeeks($week - 1)->format('Y-m-d');
        };

        // 春分の日・秋分の日の簡易計算
        $getEquinoxDay = function($year, $type) {
            if ($type === 'spring') {
                $day = floor(20.6911 + 0.242194 * ($year - 2000) - floor(($year - 2000) / 4));
                return Carbon::create($year, 3, $day)->format('Y-m-d');
            } else {
                $day = floor(23.0900 + 0.242194 * ($year - 2000) - floor(($year - 2000) / 4));
                return Carbon::create($year, 9, $day)->format('Y-m-d');
            }
        };

        // 固定祝日
        $fixedHolidays = [
            "$year-01-01", // 元日
            "$year-02-11", // 建国記念の日
            "$year-02-23", // 天皇誕生日
            "$year-04-29", // 昭和の日
            "$year-05-03", // 憲法記念日
            "$year-05-04", // みどりの日
            "$year-05-05", // こどもの日
            "$year-08-11", // 山の日
            "$year-11-03", // 文化の日
            "$year-11-23", // 勤労感謝の日
        ];

        // 移動祝日
        $movingHolidays = [
            $getHappyMonday($year, 1, 2),    // 成人の日
            $getHappyMonday($year, 7, 3),    // 海の日
            $getHappyMonday($year, 9, 3),    // 敬老の日
            $getHappyMonday($year, 10, 2),   // スポーツの日
            $getEquinoxDay($year, 'spring'), // 春分の日
            $getEquinoxDay($year, 'autumn'), // 秋分の日
        ];

        $allHolidays = array_merge($fixedHolidays, $movingHolidays);

        // 振替休日の判定
        $substituteHolidays = [];
        foreach ($allHolidays as $h) {
            $carbonH = Carbon::parse($h);
            if ($carbonH->isSunday()) {
                $substitute = $carbonH->addDay();
                while (in_array($substitute->format('Y-m-d'), $allHolidays)) {
                    $substitute->addDay();
                }
                $substituteHolidays[] = $substitute->format('Y-m-d');
            }
        }

        // 国民の休日（祝日と祝日に挟まれた平日）の判定
        $nationalHolidays = [];
        sort($allHolidays);
        for ($i = 0; $i < count($allHolidays) - 1; $i++) {
            $d1 = Carbon::parse($allHolidays[$i]);
            $d2 = Carbon::parse($allHolidays[$i+1]);
            if ($d1->diffInDays($d2) === 2 && !$d1->isSunday()) {
                $nationalHolidays[] = $d1->addDay()->format('Y-m-d');
            }
        }

        $finalHolidays = array_unique(array_merge($allHolidays, $substituteHolidays, $nationalHolidays));

        // 表示中月の日付のみを抽出し、配列のキーを連番に振り直して返却
        $filteredHolidays = array_filter($finalHolidays, function($h) use ($month) {
            return strpos($h, $month->format('Y-m')) === 0;
        });

        return array_values($filteredHolidays);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'date'       => 'required|date',
            'action'     => 'required|in:register,delete',
            'start_hour' => 'required_if:action,register|string',
            'end_hour'   => 'required_if:action,register|string',
        ]);

        if ($request->action === 'register') {
            $startTime = Carbon::parse($request->date . ' ' . $request->start_hour);
            $endTime = Carbon::parse($request->date . ' ' . $request->end_hour);

            if ($endTime->lt($startTime)) {
                return redirect()->back()->with('error', '退勤時間は出勤時間より後の時間を設定してください。');
            }

            Shift::updateOrCreate(
                [
                    'user_id'    => $request->user_id,
                    'shift_date' => $request->date,
                ],
                [
                    'start_time' => $startTime,
                    'end_time'   => $endTime,
                ]
            );
            $message = 'シフトを登録しました（' . $request->start_hour . '〜' . $request->end_hour . '）。';
        } else {
            Shift::where('user_id', $request->user_id)
                 ->where('shift_date', $request->date)
                 ->delete();
            $message = '出勤を取り消しました。';
        }

        $month = Carbon::parse($request->date)->format('Y-m');
        return redirect()->route('shifts.shift', ['month' => $month, 'user_id' => $request->user_id])
                         ->with('success', $message);
    }

    public function storeBulk(Request $request)
    {
        $userId = $request->user_id;
        $dates = $request->selected_dates; // ['2026-07-01', '2026-07-02'...]
        $workLocation = $request->work_location;
        
        $startTimeStr = $request->bulk_start_hour; // "17:00"
        $endTimeRaw = $request->bulk_end_hour;     // ここに "33:00" や "09:00+1" が入ってくる

        foreach ($dates as $dateStr) {
            // 開始日時（これはエラーにならない）
            $start = \Carbon\Carbon::parse($dateStr . ' ' . $startTimeStr);
            
            // 🚨【修正箇所】ここから ───
            $endTimeStr = $endTimeRaw;
            $addDays = 0;

            // もし画面から「09:00+1」のように送られてきた場合の分解処理
            if (strpos($endTimeRaw, '+') !== false) {
                list($endTimeStr, $addDays) = explode('+', $endTimeRaw);
                $addDays = (int)$addDays;
            }

            // 「:」で区切って時と分に分解し、33:00 などの不正な時間をクレンジングする
            if (strpos($endTimeStr, ':') !== false) {
                list($hours, $minutes) = explode(':', $endTimeStr);
                $hours = (int)$hours;

                if ($hours >= 24) {
                    // 33:00 のような表記なら、24を引いて「翌日の09:00」に変換する
                    $nextDayHours = $hours - 24;
                    $end = \Carbon\Carbon::parse($dateStr . ' ' . sprintf('%02d:%s', $nextDayHours, $minutes))->addDay();
                } else {
                    // 通常の時間（09:00など）
                    $end = \Carbon\Carbon::parse($dateStr . ' ' . $endTimeStr);
                    // 画面から「09:00+1」で送られてきていたら1日足す
                    if ($addDays > 0) {
                        $end->addDays($addDays);
                    }
                }
            } else {
                $end = \Carbon\Carbon::parse($dateStr . ' ' . $endTimeStr);
            }
            // ─── ここまで 🚨

            // データベースへ保存
            \App\Models\Shift::updateOrCreate(
                ['user_id' => $userId, 'shift_date' => $dateStr],
                [
                    'work_location' => $workLocation,
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'), 
                ]
            );
        }

        return redirect()->back()->withInput()->with('success', '一括登録しました。');
    }
}