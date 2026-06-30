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

        // ★追加：画面で選択されたユーザーID（指定がなければ最初のユーザー）
        $selectedUserId = $request->input('user_id');
        $users = User::all();
        $selectedUser = $selectedUserId ? User::find($selectedUserId) : $users->first();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // ★追加：カレンダーのグリッド配置用（1日の曜日と、末日の曜日）
        // 0 (日曜日) から 6 (土曜日) 
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

        return view('shifts.shift', compact('users', 'selectedUser', 'dates', 'currentMonth', 'startOfWeek', 'shifts'));
    }

public function store(Request $request)
    {
        // バリデーションに start_hour と end_hour を追加
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'date'       => 'required|date',
            'action'     => 'required|in:register,delete',
            'start_hour' => 'required_if:action,register|string',
            'end_hour'   => 'required_if:action,register|string',
        ]);

        if ($request->action === 'register') {
            // 画面から送られてきた時間（"09:00" など）と日付を組み合わせてCarbonインスタンスを作る
            $startTime = Carbon::parse($request->date . ' ' . $request->start_hour);
            $endTime = Carbon::parse($request->date . ' ' . $request->end_hour);

            // 退勤時間が出勤時間より前の場合はエラーにする簡易チェック
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
}