<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>打刻修正申請</title>
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f3f4f6; color: #333; }
        .card { background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    @include('layouts.header')

<div class="layout">

    @include('layouts.sidebar')

    <div class="wrap">

<div class="card">
    <h2>打刻修正申請</h2>

    {{-- 「日付切り替え」 --}}
    <div class="mb-6 px-1 flex flex-col sm:flex-row sm:items-center justify-center gap-3 bg-slate-50 p-5 rounded-2xl">
        <form method="GET" action="{{ route('dakoku.request.create') }}" id="dateForm" class="flex items-center justify-center gap-2 w-full">
            @if(request('user_id'))
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
            @endif
            
            <div class="flex items-center bg-white border-2 border-black rounded-2xl shadow-2xs overflow-hidden h-14 min-w-[280px]">
                {{-- ◀ 前の日ボタン --}}
                <button type="button" onclick="changeDate(-1)" class="w-14 h-full hover:bg-slate-50 text-black transition-colors cursor-pointer font-bold border-r-2 border-black text-xl flex items-center justify-center">
                    &lt;
                </button>
                
                {{-- 日付選択インプット (type="date"に変更) --}}
                <input type="date" name="date" id="dateInput" value="{{ $date }}" onchange="document.getElementById('dateForm').submit()" class="px-6 py-2 bg-transparent text-xl font-bold text-slate-800 focus:outline-none cursor-pointer tracking-wide text-center">
                
                {{-- ▶ 次の日ボタン --}}
                <button type="button" onclick="changeDate(1)" class="w-14 h-full hover:bg-slate-50 text-black transition-colors cursor-pointer font-bold border-l-2 border-black text-xl flex items-center justify-center">
                    &gt;
                </button>
            </div>
        </form>
    </div>

    <div class="current-data" style="padding: 15px; border-radius: 4px; margin-bottom: 20px;">
        <strong>現在の打刻データ:</strong><br>
            出勤: {{ $currentAttendance->check_in ? \Carbon\Carbon::parse($currentAttendance->check_in)->format('H:i') : '未打刻' }} / 
            退勤: {{ $currentAttendance->check_out ? \Carbon\Carbon::parse($currentAttendance->check_out)->format('H:i') : '未打刻' }}
    </div>

    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

    @if($pendingRequest)
        <div class="alert-warning">
            <strong>申請中:</strong> この日はすでに管理者へ修正申請を出しています。
        </div>
    @else

        @if($currentAttendance && ($currentAttendance->check_in || $currentAttendance->check_out))
    
            <form action="{{ route('dakoku.request.store') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                @if($currentAttendance->check_in)
                    <div style="padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                        <h3>出勤</h3>
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label for="requested_punch_in">正しい出勤時間</label>
                            <input type="time" id="requested_punch_in" name="requested_punch_in" 
                                value="{{ old('requested_punch_in', \Carbon\Carbon::parse($currentAttendance->check_in)->format('H:i')) }}"
                                class="w-full p-2 border rounded">
                        </div>
                        <div class="form-group">
                            <label for="reason_in">出勤の申請理由 <span style="color:red; font-size:12px;">(必須)</span></label>
                            <textarea id="reason_in" name="reason_in" rows="2" placeholder="例：出勤時の打刻漏れ" class="w-full p-2 border rounded">{{ old('reason_in') }}</textarea>
                        </div>
                    </div>
                @endif

                @if($currentAttendance->check_out)
                    <div style="padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                        <h3>退勤</h3>
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label for="requested_punch_out">正しい退勤時間</label>
                            <input type="time" id="requested_punch_out" name="requested_punch_out" 
                                value="{{ old('requested_punch_out', \Carbon\Carbon::parse($currentAttendance->check_out)->format('H:i')) }}"
                                class="w-full p-2 border rounded">
                        </div>
                        <div class="form-group">
                            <label for="reason_out">退勤の申請理由 <span style="color:red; font-size:12px;">(必須)</span></label>
                            <textarea id="reason_out" name="reason_out" rows="2" placeholder="例：残業したが押し忘れた" class="w-full p-2 border rounded">{{ old('reason_out') }}</textarea>
                        </div>
                    </div>
                @endif

                <button type="submit" style="background: #28a745; color: white;" class="w-full p-3 rounded-xl font-bold cursor-pointer">
                    この内容で一括申請する
                </button>
            </form>

        @else
            <div style="text-align: center; color: #666; padding: 30px 0;">
                <p>この日の打刻データがないため、修正申請は行えません。</p>
            </div>
        @endif
    @endif
</div>

<script>
    function changeDate(steps) {
        const input = document.getElementById('dateInput');
        if (!input.value) return;

        // 現在選択されている日付をJavaScriptのDateオブジェクトにする
        const currentDate = new Date(input.value);
        
        // 日数を増減させる（+1日 または -1日）
        currentDate.setDate(currentDate.getDate() + steps);

        // 日本時間のタイムゾーンを考慮して YYYY-MM-DD 形式の文字列に変換
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');
        
        // インプットの値を書き換えて自動送信
        input.value = `${year}-${month}-${day}`;
        document.getElementById('dateForm').submit();
    }
</script>