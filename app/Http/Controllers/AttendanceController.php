<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notice;

class AttendanceController extends Controller
{
    // ダッシュボード表示（GET /dashboard）
public function dashboard()
{
    $attendance = Attendance::where('user_id', Auth::id())
        ->where('work_date', today())
        ->first();

    $notices = Notice::latest()->get();

    $todayShift = Shift::where('user_id', Auth::id())
    ->whereDate('shift_date', today())
    ->first();

    


        return view('dashboard', [
            'attendance'  => $attendance,
            'status'      => $this->resolveStatus($attendance),
            'workMinutes' => $this->workMinutes($attendance),
            'notices'     => $notices,
            'todayShift'  => $todayShift,
        ]);
}

    // 打刻（POST /attendance/punch）
    public function punch(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:check_in,check_out,break_start,break_end',
        ]);

        $type = $validated['type'];

        // 当日のレコードを取得。無ければ作成
        $attendance = Attendance::firstOrCreate([
            'user_id'   => Auth::id(),
            'work_date' => today(),
        ]);

        // 打刻順の妥当性チェック。不正ならメッセージを返して中断
        if ($error = $this->validatePunch($type, $attendance)) {
            return back()->with('error', $error);
        }

        $attendance->{$type} = now();
        $attendance->save();

        $labels = [
            'check_in'    => '出勤',
            'check_out'   => '退勤',
            'break_start' => '休憩開始',
            'break_end'   => '休憩終了',
        ];
        return back()->with('status', $labels[$type] . 'を記録しました（' . now()->format('H:i') . '）');
    }

    // 打刻履歴(GET /attendance/history)
    public function history()
    {

        $userId = Auth::id();
        $from = today()->subDays(30);
        $to = today();

        // 直近30日分を新しい順で所得
        $records = Attendance::where('user_id', $userId)
            ->where('work_date', '>=', $from)
            ->get()
            ->keyBy(fn($a) => $a->work_date->format('Y-m-d'));

        $shifts = Shift::where('user_id', $userId)
            ->where('shift_date', '>=', $from->format('Y-m-d'))
            ->get()
            ->keyBy(fn($s) => Carbon::parse($s->shift_date)->format('Y-m-d'));
        
        $approvedAbsences = \App\Models\AttendanceRequest::where('user_id', $userId)
            ->where('type', 'absence')
            ->where('status', 'approved')
            ->where('target_date', '>=', $from->format('Y-m-d'))
            ->get()
            ->keyBy(fn ($r) => \Carbon\Carbon::parse($r->target_date)->format('Y-m-d'));

        // 各レコードに勤務時間(分)を持たせる。
        // 日付を軸に,新しい順で1日づつ組み立てる。
        $rows = collect();
        for ($date = $to->copy(); $date->gte($from); $date->subDay()) {
            $key = $date->format('Y-m-d');

            $attendance = $records->get($key);
            $shift = $shifts->get($key);
            $isApprovedAbsence = $approvedAbsences -> has($key);

            // 予定も実績もない日は行をつくらない。
            if (!$attendance && !$shift && !$isApprovedAbsence) {
                continue;
            }

            $rows->push([    
                'date' => $date->copy(),
                'record' => $attendance,
                'shift' => $shift,
                'workMinutes' => $this->workMinutes($attendance),
                'state' => $this->dayState($attendance, $shift, $isApprovedAbsence),
                'diff' => $this->calcDiff($attendance, $shift),
            ]);
        }

        return view('attendance.history', [
            'rows' => $rows,
        ]);
    }

    // 予定と実績の差分(遅刻・早退・残業)を分単位で計算する。
    // 予定と出退勤が揃っていない項目はnull 判定しない
    private function calcDiff(?Attendance $a, $shift): array 
    {
        $late = null;       // 遅刻(分)
        $early = null;      // 早退(分)
        $overtime = null;    // 残業(分)

        // 予定がない、または出退勤がなければ差分はださない。
        if (!$shift || !$a) {
            return ['late' => null, 'early' => null, 'overtime' => null];
        }

        // 秒を切り捨てて「分」比較するためのヘルパ
        $toMin = fn ($dt) => Carbon::parse($dt)->startOfMinute();

        // 遅刻:実際の出勤 > 予定開始
        if ($a -> check_in) {
            $planStart = $toMin($shift->start_time);
            $realIn = $toMin($a->check_in);
            if ($realIn -> gt($planStart)) {
                $late = (int) abs($planStart->diffInMinutes($realIn));
            }
        }

        // 早退・残業:退勤が予定終了より前なら早退、あとなら残業
        if ($a->check_out) {
            $planEnd = $toMin($shift->end_time);
            $realOut = $toMin($a->check_out);
            if ($realOut->lt($planEnd)) {
                $early = (int) abs($planEnd->diffInMinutes($realOut));
            } elseif ($realOut->gt($planEnd)) {
                $overtime = (int) abs($realOut->diffInMinutes($planEnd));
            }
        }

        return ['late' => $late, 'early' => $early, 'overtime' => $overtime];
    }

    // その日の状態を判定する(欠勤/未打刻/勤務中/退勤済み/予定外)
    private function dayState(?Attendance $a, $shift, bool $isApprovedAbsence = false): string
    {
        // 出勤打刻がない日
        if (!$a || !$a->check_in){
            // 承認済みの欠勤申請があれば[承認済み欠勤],なければ状況に応じて判断
            if ($isApprovedAbsence){
                return '承認済み欠勤';
            }

            // シフトがあるのに打刻も承認欠勤もなし -> 無断欠勤
            if ($shift) {
                return '無断欠勤';
            }

            // シフトも打刻もない日(そもそも勤務予定なし)
            return '---';
        }

        if ($a->check_out) return '退勤済み';
        if ($a->break_start && !$a->break_end) return '休憩中';
        return '勤務中';
    }

    // 打刻順の検証。問題があればエラーメッセージ、無ければ null
    private function validatePunch(string $type, Attendance $a): ?string
    {
        return match ($type) {
            'check_in' => $a->check_in
                ? '既に出勤打刻済みです。'
                : null,
            'check_out' => match (true) {
                !$a->check_in                     => '先に出勤打刻をしてください。',
                (bool) $a->check_out              => '既に退勤打刻済みです。',
                $a->break_start && !$a->break_end => '休憩終了を打刻してから退勤してください。',
                default                           => null,
            },
            'break_start' => match (true) {
                !$a->check_in          => '先に出勤打刻をしてください。',
                (bool) $a->check_out   => '退勤後は休憩できません。',
                (bool) $a->break_start => '既に休憩開始を打刻済みです。',
                default                => null,
            },
            'break_end' => match (true) {
                !$a->break_start     => '先に休憩開始を打刻してください。',
                (bool) $a->break_end => '既に休憩終了を打刻済みです。',
                default              => null,
            },
            default => '不明な打刻種別です。',
        };
    }

    // 現在の勤務状態ラベル
    private function resolveStatus(?Attendance $a): string
    {
        if (!$a || !$a->check_in)              return '未出勤';
        if ($a->check_out)                     return '退勤済み';
        if ($a->break_start && !$a->break_end) return '休憩中';
        return '勤務中';
    }

    // 当日の勤務時間（分）=（退勤 - 出勤）- 休憩。出退勤が揃うまでは null
    private function workMinutes(?Attendance $a): ?int
    {
        if (!$a || !$a->check_in || !$a->check_out) {
            return null;
        }
        // abs() でCarbonのバージョン差（符号の向き）に依存しないようにする
        $minutes = (int) abs($a->check_in->diffInMinutes($a->check_out));
        if ($a->break_start && $a->break_end) {
            $minutes -= (int) abs($a->break_start->diffInMinutes($a->break_end));
        }
        return max(0, $minutes);
    }
public function updateLocation(Request $request)
{
    $request->validate([
        'work_location' => 'required|in:本社,研修（出社）,常駐先（出社）',
    ]);

    $shift = Shift::where('user_id', Auth::id())
        ->whereDate('shift_date', today())
        ->first();

    if ($shift) {

        $shift->update([
            'work_location' => $request->work_location,
        ]);

    }

    return redirect()
        ->route('dashboard')
        ->with('success', '勤務地を変更しました');
}

}
