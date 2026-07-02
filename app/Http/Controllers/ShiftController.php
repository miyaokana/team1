<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $selectedUser = Auth::user();

        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthInput);

        // ログイン中のユーザーのシフトを取得
        $shifts = Shift::where('user_id', $selectedUser->id)
        ->whereMonth('shift_date', $currentMonth->month)
        ->whereYear('shift_date', $currentMonth->year)
        ->get();

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

        return view('shifts.shift', compact('selectedUser', 'currentMonth', 'shifts', 'dates', 'startOfWeek', 'holidays'));
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

        // 表示中月の日付のみを抽出
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
        // 💡 改善: 適切にバリデーションを追加
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'selected_dates'   => 'required|array',
            'selected_dates.*' => 'required|date',
            'action'           => 'required|in:register,delete',
            'bulk_start_hour'  => 'required_if:action,register|string',
            'bulk_end_hour'    => 'required_if:action,register|string',
            'work_location'    => 'nullable|string',
        ]);

        $userId = Auth::id();
        $dates = $request->selected_dates;

        if ($request->action === 'delete') {
            Shift::where('user_id', $userId)
                ->whereIn('shift_date', $dates)
                ->delete();

            return redirect()->back()->with('success', '選択した日付のシフトを一括削除しました。');
        }

        $workLocation = $request->work_location;
        $startTimeStr = $request->bulk_start_hour; // "17:00"
        $endTimeRaw = $request->bulk_end_hour;     // "33:00" や "09:00+1"

        foreach ($dates as $dateStr) {
            $start = Carbon::parse($dateStr . ' ' . $startTimeStr);
            
            // 💡 改善: 終了時間の解析ロジックを Carbon を活かしてシンプル化
            $endTimeStr = $endTimeRaw;
            $addDays = 0;

            // 「+1」などの日数加算表記のパース
            if (strpos($endTimeRaw, '+') !== false) {
                list($endTimeStr, $addDays) = explode('+', $endTimeRaw);
                $addDays = (int)$addDays;
            }

            // 「33:00」などの24時間超過表記のパース
            if (strpos($endTimeStr, ':') !== false) {
                list($hours, $minutes) = explode(':', $endTimeStr);
                $hours = (int)$hours;

                if ($hours >= 24) {
                    $addDays += floor($hours / 24); // 24時間ごとに1日加算
                    $hours = $hours % 24;           // 24未満の余り時間に変換
                }
                $endTimeStr = sprintf('%02d:%s', $hours, $minutes);
            }

            // ベースとなる終了日時を生成し、算出した日数を加算
            $end = Carbon::parse($dateStr . ' ' . $endTimeStr)->addDays($addDays);

            // 💡 修正バグ対応: format('H:i:s') ではなく、日付を含んだ Carbon オブジェクト、
            // または format('Y-m-d H:i:s') で保存する
            Shift::updateOrCreate(
                ['user_id' => $userId, 'shift_date' => $dateStr],
                [
                    'work_location' => $workLocation,
                    'start_time'    => $start, // もしDBが time型 の場合は $start->format('H:i:s') に戻し、
                    'end_time'      => $end,   // 別途「翌日フラグ」などをDBに持たせる必要があります
                ]
            );
        }

        return redirect()->back()->withInput()->with('success', '一括登録しました。');
    }
}