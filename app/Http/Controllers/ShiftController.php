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
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date'    => 'required|date',
            'action'  => 'required|in:register,delete', // 登録か削除かを識別
        ]);

        if ($request->action === 'register') {
            // デフォルトの勤務時間を設定 (例: 9:00 〜 17:30)
            // 送られてきた日付（例: 2026-07-01）に時間を結合します
            $startTime = Carbon::parse($request->date)->setTime(9, 0, 0);
            $endTime = Carbon::parse($request->date)->setTime(17, 3, 0);

            // データの登録（すでにあれば上書き更新、なければ新規作成）
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
            $message = '出勤を登録しました（09:00〜17:30）。';
        } else {
            // 削除処理
            Shift::where('user_id', $request->user_id)
                 ->where('shift_date', $request->date)
                 ->delete();
            $message = '出勤を取り消しました。';
        }

        $month = Carbon::parse($request->date)->format('Y-m');
        return redirect()->route('shifts.shift', ['month' => $month])
                         ->with('success', $message);
    }
}