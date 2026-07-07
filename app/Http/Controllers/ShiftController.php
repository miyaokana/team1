<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        // リクエストに user_id があればそのユーザー、なければログインユーザーを取得
        $userId = $request->input('user_id', Auth::id());
        $selectedUser = User::find($userId) ?? Auth::user();

        if (!$selectedUser) {
            return redirect()->route('login')->with('error', 'ユーザーが特定できません。');
        }

        $monthInput = $request->input('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthInput);

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // カレンダーのグリッド配置用（1日の曜日）
        $startOfWeek = $startOfMonth->dayOfWeek; 

        $dates = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()){
            $dates[] = $date->copy();
        }

        $shifts = Shift::where('user_id', $selectedUser->id)
            ->whereBetween('shift_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // 祝日の配列を取得
        $holidays = $this->getHolidaysForMonth($currentMonth);

        return view('shifts.shift', compact('selectedUser', 'currentMonth', 'shifts', 'dates', 'startOfWeek', 'holidays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'date'               => 'required|date',
            'action'             => 'required|in:register,delete',
            'start_hour'         => 'required_if:action,register|string',
            'end_hour'           => 'required_if:action,register|string',
            'break_minutes'      => 'required_if:action,register|integer|min:0',
            'work_location_base' => 'required_if:action,register|string',
            'work_style'         => 'required_if:action,register|string', 
        ]);

        if ($request->action === 'delete') {
            Shift::where('user_id', $request->user_id)
                 ->where('shift_date', $request->date)
                 ->delete();
                 
            $message = '出勤を取り消しました。';
        } else {
            $startTime = Carbon::parse($request->date . ' ' . $request->start_hour);
            // 単発登録でも変則的な終了時間（夜勤など）に対応できるように共通メソッドを使用
            $endTime = $this->parseEndTime($request->date, $request->end_hour);

            if ($endTime->lt($startTime)) {
                return redirect()->back()->with('error', '退勤時間は出勤時間より後の時間を設定してください。');
            }

            $totalWorkingMinutes = $startTime->diffInMinutes($endTime);
            if ($request->break_minutes > $totalWorkingMinutes) {
                return redirect()->back()->with('error', '休憩時間は総勤務時間より短く設定してください。');
            }

            $base = $request->input('work_location_base', '本社');
            $style = $request->input('work_style', '出社');
            $workLocation = "{$base}（{$style}）";

            Shift::updateOrCreate(
                [
                    'user_id'    => $request->user_id,
                    'shift_date' => $request->date,
                ],
                [
                    'work_location' => $workLocation,
                    'start_time'    => $startTime,
                    'end_time'      => $endTime,
                    'break_minutes' => $request->break_minutes
                ]
            );
            $message = 'シフトを登録しました（' . $request->start_hour . '〜' . $request->end_hour . '）。';
        }

        $month = Carbon::parse($request->date)->format('Y-m');
        return redirect()->route('shifts.shift', ['month' => $month, 'user_id' => $request->user_id])
                         ->with('success', $message);
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'selected_dates'     => 'required|array',
            'selected_dates.*'   => 'required|date',
            'action'             => 'required|in:register,delete',
            'bulk_start_hour'    => 'required_if:action,register|string',
            'bulk_end_hour'      => 'required_if:action,register|string',
            'bulk_break_minutes' => 'required_if:action,register|integer|min:0',
            'work_location_base' => 'required_if:action,register|string',
            'work_style'         => 'required_if:action,register|string',
        ]);

        // 1. 開始時間と終了時間を取得
        $start = $request->input('bulk_start_hour'); // "09:00"
        $endRaw = $request->input('bulk_end_hour');   // "17:30+0" などの想定

        if ($start && $endRaw) {
            // 翌日フラグの有無を確認
            $isNextDay = str_contains($endRaw, '+1');
            $end = str_replace(['+0', '+1'], '', $endRaw);

            $startTime = \Carbon\Carbon::parse($start);
            $endTime = \Carbon\Carbon::parse($end);

            // 翌日の場合は1日加算
            if ($isNextDay || $endTime->lt($startTime)) {
                $endTime->addDay();
            }

            // 差分（時間）を計算
            $diffHours = $startTime->diffInHours($endTime);

            // 6時間以上なら60分、それ未満なら0分をリクエストに強制追加
            $bulkBreak = ($diffHours >= 6) ? 60 : 0;
            
            $request->merge(['bulk_break_minutes' => $bulkBreak]);
        }

        // 2. ここでバリデーションを行う（すでに値が入っているので required を通過します）
        $request->validate([
            'bulk_break_minutes' => 'required|integer',
            // 他のバリデーション...
        ]);

        $userId = $request->user_id; 
        $dates = $request->selected_dates;

        if ($request->action === 'delete') {
            Shift::where('user_id', $userId)
                ->whereIn('shift_date', $dates)
                ->delete();

            return redirect()->back()->with('success', '選択した日付のシフトを一括削除しました。');
        }

        $base = $request->input('work_location_base', '本社');
        $style = $request->input('work_style', '出社');
        $workLocation = "{$base}（{$style}）";
        
        $startTimeStr = $request->bulk_start_hour; 
        $endTimeRaw = $request->bulk_end_hour;     
        $breakMinutes = $request->bulk_break_minutes;

        // 💡 データの詰め替えを行い、upsertで一括処理（高速化 ＆ foreach内のエラーを排除）
        $upsertData = [];
        foreach ($dates as $dateStr) {
            $start = Carbon::parse($dateStr . ' ' . $startTimeStr);
            $end = $this->parseEndTime($dateStr, $endTimeRaw);

            $upsertData[] = [
                'user_id'       => $userId,
                'shift_date'    => $dateStr,
                'work_location' => $workLocation,
                'start_time'    => $start->format('Y-m-d H:i:s'),
                'end_time'      => $end->format('Y-m-d H:i:s'),
                'break_minutes' => $breakMinutes,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        DB::transaction(function () use ($upsertData) {
            Shift::upsert(
                $upsertData, 
                ['user_id', 'shift_date'], 
                ['work_location', 'start_time', 'end_time', 'break_minutes', 'updated_at']
            );
        });

        return redirect()->back()->withInput()->with('success', '一括登録しました。');
    }

    /**
     * 「33:00」や「10:00+1」などの変則的な退勤時間をパースする共通ロジック
     */
    private function parseEndTime(string $dateStr, string $endTimeRaw): Carbon
    {
        $endTimeStr = $endTimeRaw;
        $addDays = 0;

        // 「+1」などの日数加算表記のパース
        if (strpos($endTimeRaw, '+') !== false) {
            [$endTimeStr, $addDays] = explode('+', $endTimeRaw);
            $addDays = (int)$addDays;
        }

        // 「33:00」などの24時間超過表記のパース
        if (strpos($endTimeStr, ':') !== false) {
            [$hours, $minutes] = explode(':', $endTimeStr);
            $hours = (int)$hours;

            if ($hours >= 24) {
                $addDays += floor($hours / 24);
                $hours = $hours % 24;
            }
            $endTimeStr = sprintf('%02d:%s', $hours, $minutes);
        }

        return Carbon::parse($dateStr . ' ' . $endTimeStr)->addDays($addDays);
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
            "$year-01-01", "$year-02-11", "$year-02-23", "$year-04-29", 
            "$year-05-03", "$year-05-04", "$year-05-05", "$year-08-11", 
            "$year-11-03", "$year-11-23",
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
}