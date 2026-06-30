<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // ダッシュボード表示（GET /dashboard）
    public function dashboard()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', today())
            ->first();
        return view('dashboard', [
            'attendance'  => $attendance,
            'status'      => $this->resolveStatus($attendance),
            'workMinutes' => $this->workMinutes($attendance),
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
}
