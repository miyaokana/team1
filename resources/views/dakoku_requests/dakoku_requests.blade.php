<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>打刻修正申請</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
</head>

<body class="font-sans m-[40px] bg-[#f3f4f6] text-[#333]">

    @include('layouts.header')

<div class="layout">

    @include('layouts.sidebar')

    <div class="wrap">

<div class="bg-white p-[30px] rounded-[8px] max-w-[700px] mx-auto shadow-[0_4px_6px_rgba(0,0,0,0.1)] border-2 border-rose-300">
    <div class="flex flex-col items-center text-center gap-2 mb-4 border-b border-slate-100 pb-5">
        <h2 class="text-4xl font-bold text-slate-900 tracking-tight">打刻修正申請</h2>
    </div>

    {{-- 「日付切り替え」 --}}
    <div class="mb-2 px-1 flex flex-col sm:flex-row sm:items-center justify-center gap-3 bg-slate-50 p-5 rounded-2xl">
        <form method="GET" action="{{ route('dakoku.request.create') }}" id="dateForm" class="flex items-center justify-center gap-2 w-full">
            @if(request('user_id'))
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
            @endif
            
            <div class="flex items-center bg-white border-2 border-black rounded-2xl shadow-2xs overflow-hidden h-14 min-w-[280px]">
                {{-- ◀ 前の日ボタン --}}
                <button type="button" onclick="changeDate(-1)" class="w-14 h-full hover:bg-slate-50 text-black transition-colors cursor-pointer font-bold border-r-2 border-black text-xl flex items-center justify-center">
                    &lt;
                </button>
                
                {{-- 日付選択インプット --}}
                <div class="flex-1 h-full relative flex items-center justify-between px-6">
                    <span id="dateDisplayText" class="text-xl font-bold text-slate-800 tracking-wide pointer-events-none">
                        {{ \Carbon\Carbon::parse($date)->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}
                    </span>

                    <span class="text-slate-400 text-lg pointer-events-none">📅</span>

                    <input type="date" name="date" id="dateInput" value="{{ $date }}" 
                        onchange="updateDateDisplay(this.value); document.getElementById('dateForm').submit();" 
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
                
                {{-- ▶ 次の日ボタン --}}
                <button type="button" onclick="changeDate(1)" class="w-14 h-full hover:bg-slate-50 text-black transition-colors cursor-pointer font-bold border-l-2 border-black text-xl flex items-center justify-center">
                    &gt;
                </button>
            </div>
        </form>
    </div>

    @if($currentAttendance && ($currentAttendance->check_in || $currentAttendance->check_out))
        <div class="current-data p-[10px] rounded-[4px] mb-[20px] items-center text-center text-lg">
            <strong class="font-bold">現在の打刻データ</strong><br>
            出勤 {{ $currentAttendance->check_in ? \Carbon\Carbon::parse($currentAttendance->check_in)->format('H:i') : '未打刻' }} / 
            退勤 {{ $currentAttendance->check_out ? \Carbon\Carbon::parse($currentAttendance->check_out)->format('H:i') : '未打刻' }}
        </div>
    @endif

    @if($pendingRequest)
        <div class="bg-amber-50 border-2 border-amber-400 text-amber-900 p-4 rounded-xl font-medium mb-4 flex items-center gap-2">
            <span>⚠️</span>
            <strong>申請中:</strong> この日はすでに管理者へ修正申請を出しています。
        </div>
    @else
        @if($currentAttendance && ($currentAttendance->check_in || $currentAttendance->check_out))
    
            <form action="{{ route('dakoku.request.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                {{-- ▼ 出勤エリア --}}
                @if($currentAttendance->check_in)
                    <div id="box_in" class="bg-emerald-50/50 border-2 border-emerald-600 rounded-2xl p-5 shadow-2xs transition-all duration-300">
                        {{-- ヘッダー：左端にタイトル、右端に削除チェック --}}
                        <div class="flex items-center justify-between gap-3 mb-4 border-b border-emerald-100 pb-3 w-full">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-6 bg-emerald-600 rounded-full"></span>
                                <h3 class="text-xl font-black text-slate-800">出勤時間の修正</h3>
                            </div>
                            <div class="flex justify-end">
                                <label class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-rose-400 text-black font-bold text-sm cursor-pointer hover:bg-rose-50 transition-colors shadow-2xs select-none">
                                    <span>削除</span>
                                    <input type="checkbox" name="delete_in" value="1" onchange="toggleDeleteMode('in')" {{ old('delete_in') ? 'checked' : '' }} class="w-4 h-4 accent-rose-600 cursor-pointer">
                                </label>
                            </div>
                        </div>
                        
                        <div id="inputs_in" class="flex flex-col gap-5 transition-opacity duration-300">
                            <div class="form-group flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                                <label for="requested_punch_in" class="text-base font-bold text-slate-800 min-w-[140px]">打刻時間</label>
                                <input type="time" id="requested_punch_in" name="requested_punch_in" 
                                    value="{{ old('requested_punch_in', \Carbon\Carbon::parse($currentAttendance->check_in)->format('H:i')) }}"
                                    class="w-full sm:w-64 h-12 px-4 bg-white border-2 border-slate-500 focus:border-black focus:outline-none rounded-xl font-bold text-lg text-slate-800 cursor-pointer transition-colors">
                            </div>
                            
                            <hr class="my-3 border-1 border-slate-400">
                            
                            <div class="form-group flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                                <label for="reason_in" class="text-base font-bold text-slate-800 min-w-[140px]">
                                    出勤の申請理由 <span id="badge_in" class="text-rose-500 font-extrabold text-xs bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200 inline-block mt-1 sm:mt-0">必須</span>
                                </label>
                                <textarea id="reason_in" name="reason_in" rows="2" placeholder="例：出勤時の打刻漏れ" 
                                    class="w-full flex-1 p-3 bg-white border-2 border-slate-500 focus:border-black focus:outline-none rounded-xl text-slate-800 placeholder-slate-400 transition-colors resize-none">{{ old('reason_in') }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ▼ 退勤エリア --}}
                @if($currentAttendance->check_out)
                    <div id="box_out" class="bg-indigo-50/50 border-2 border-indigo-600 rounded-2xl p-5 shadow-2xs transition-all duration-300">
                        {{-- ヘッダー：左端にタイトル、右端に削除チェック --}}
                        <div class="flex items-center justify-between gap-3 mb-4 border-b border-indigo-100 pb-3 w-full">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-6 bg-indigo-600 rounded-full"></span>
                                <h3 class="text-xl font-black text-slate-800">退勤時間の修正</h3>
                            </div>
                            <div class="flex justify-end">
                                <label class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-rose-400 text-black font-bold text-sm cursor-pointer hover:bg-rose-50 transition-colors shadow-2xs select-none">
                                    <span>削除</span>
                                    <input type="checkbox" name="delete_out" value="1" onchange="toggleDeleteMode('out')" {{ old('delete_out') ? 'checked' : '' }} class="w-4 h-4 accent-rose-600 cursor-pointer">
                                </label>
                            </div>
                        </div>
                        
                        <div id="inputs_out" class="flex flex-col gap-5 transition-opacity duration-300">
                            <div class="form-group flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                                <label for="requested_punch_out" class="text-base font-bold text-slate-800 min-w-[140px]">打刻時間</label>
                                <input type="time" id="requested_punch_out" name="requested_punch_out" 
                                    value="{{ old('requested_punch_out', \Carbon\Carbon::parse($currentAttendance->check_out)->format('H:i')) }}"
                                    class="w-full sm:w-64 h-12 px-4 bg-white border-2 border-slate-500 focus:border-black focus:outline-none rounded-xl font-bold text-lg text-slate-800 cursor-pointer transition-colors">
                            </div>
                            
                            <hr class="my-3 border-1 border-slate-400">
                            
                            <div class="form-group flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                                <label for="reason_out" class="text-base font-bold text-slate-800 min-w-[140px]">
                                    退勤の申請理由 <span id="badge_out" class="text-rose-500 font-extrabold text-xs bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200 inline-block mt-1 sm:mt-0">必須</span>
                                </label>
                                <textarea id="reason_out" name="reason_out" rows="2" placeholder="例：残業したが押し忘れた" 
                                    class="w-full flex-1 p-3 bg-white border-2 border-slate-500 focus:border-black focus:outline-none rounded-xl text-slate-800 placeholder-slate-400 transition-colors resize-none">{{ old('reason_out') }}</textarea>
                            </div>
                        

                            <hr class="my-3 border-1 border-slate-400">

                            <div class="form-group flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4 mt-3">
                                <label for="auto_break_out" class="text-base font-bold text-slate-800 min-w-[140px]">休憩時間の調整</label>
                                <div class="w-full sm:w-72">
                                    <select id="auto_break_out" name="auto_break_out" 
                                        class="w-full h-12 px-4 bg-white border-2 border-slate-500 focus:border-black focus:outline-none rounded-xl font-bold text-base text-slate-800 cursor-pointer transition-colors">
                                        <option value="0" {{ old('auto_break_out') == '0' ? 'selected' : '' }}>休憩時間を自動追加しない</option>
                                        <option value="1" {{ old('auto_break_out') == '1' ? 'selected' : '1' }}>勤務時間に応じて自動追加する</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ▼ 送信ボタン（元のカラーを維持して中央寄せ） --}}
                <button type="submit" class="w-70 h-14 mx-auto bg-rose-400 hover:bg-rose-700 text-white font-black text-lg rounded-2xl shadow-sm transition-all cursor-pointer flex items-center justify-center tracking-wider block">
                    申請
                </button>
            </form>

        @else
            <div class="text-center text-slate-400 py-10 font-medium">
                <span class="text-4xl block mb-2">📁</span>
                <p>この日の打刻データがないため、修正申請は行えません。</p>
            </div>
        @endif
    @endif
</div>

<div class="bg-white p-[30px] rounded-[8px] max-w-[700px] mx-auto shadow-[0_4px_6px_rgba(0,0,0,0.1)] mt-[20px] border-2 border-rose-300">
    
    <h3 class="text-2xl font-bold text-slate-900 tracking-tight mb-4 flex items-center gap-2">
        <span>📋</span> 申請履歴一覧
    </h3>

    @if($historyRequests->isEmpty())
        <div class="text-center text-slate-400 py-8 font-medium bg-slate-50 rounded-2xl border border-dashed border-slate-200">
            <p>過去の打刻申請履歴はありません。</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-2xs">
            <table class="w-full text-left border-collapse bg-white">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold text-sm">
                        <th class="p-4 w-[110px]">対象日</th>
                        <th class="p-4 w-[90px]">種別</th>
                        <th class="p-4 w-[120px]">申請内容</th>
                        <th class="p-4">申請理由</th>
                        <th class="p-4 w-[90px] text-center">状態</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    @foreach($historyRequests as $req)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- 対象日 --}}
                            <td class="p-4 font-bold text-slate-800 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($req->date)->locale('ja')->isoFormat('M/D(ddd)') }}
                            </td>
                            
                            {{-- 申請種別 (出勤 or 退勤) --}}
                            <td class="p-4 whitespace-nowrap">
                                @if($req->is_in_request)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">出勤</span>
                                @elseif($req->is_out_request)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">退勤</span>
                                @endif
                            </td>
                            
                            {{-- 申請内容 (修正時刻 or 削除) --}}
                            <td class="p-4 font-mono font-bold whitespace-nowrap">
                                @if($req->is_delete)
                                    <span class="text-rose-600">打刻削除</span>
                                @else
                                    <span class="text-slate-800">
                                        {{ $req->is_in_request ? \Carbon\Carbon::parse($req->requested_punch_in)->format('H:i') : \Carbon\Carbon::parse($req->requested_punch_out)->format('H:i') }}
                                    </span>
                                    <span class="text-xs text-slate-400 font-normal ml-0.5">修正</span>
                                @endif
                            </td>
                            
                            {{-- 申請理由 --}}
                            <td class="p-4 max-w-[200px] break-words">
                                <div class="text-slate-700">{{ $req->reason }}</div>
                                @if($req->admin_comment)
                                    <div class="text-xs text-slate-400 mt-1 bg-slate-50 p-2 rounded border border-slate-200">
                                        <strong class="text-slate-600">管理者:</strong> {{ $req->admin_comment }}
                                    </div>
                                @endif
                            </td>
                            
                            {{-- 状態 (ステータス) --}}
                            <td class="p-4 text-center whitespace-nowrap">
                                @if($req->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">申請中</span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">承認済</span>
                                @elseif($req->status === 'rejected')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">却下</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

    </div>
</div>

<script>
    function changeDate(steps) {
        const input = document.getElementById('dateInput');
        if (!input.value) return;

        const currentDate = new Date(input.value);
        currentDate.setDate(currentDate.getDate() + steps);

        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');
        
        input.value = `${year}-${month}-${day}`;
        document.getElementById('dateForm').submit();
    }

    function updateDateDisplay(dateString) {
        if (!dateString) return;
        
        const date = new Date(dateString);
        const weekdays = ["日", "月", "火", "水", "木", "金", "土"];
        
        const year = date.getFullYear();
        const month = date.getMonth() + 1;
        const day = date.getDate();
        const weekday = weekdays[date.getDay()];
        
        document.getElementById('dateDisplayText').innerText = `${year}年${month}月${day}日(${weekday})`;
    }

    function toggleDeleteMode(type) {
        const checkbox = document.querySelector(`input[name="delete_${type}"]`);
        const containerBox = document.getElementById(`box_${type}`);
        const timeInput = document.getElementById(`requested_punch_${type}`);
        const badge = document.getElementById(`badge_${type}`);
        
        // 👇 退勤側の自動休憩セレクトボックスを取得
        const autoBreakSelect = document.getElementById('auto_break_out');

        if (!checkbox || !containerBox || !timeInput || !badge) return;

        if (checkbox.checked) {
            timeInput.readOnly = true;
            timeInput.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed', 'opacity-50');
            
            // 👇 削除時は「自動追加しない」にして操作不可にする
            if (type === 'out' && autoBreakSelect) {
                autoBreakSelect.value = "0";
                autoBreakSelect.disabled = true;
                autoBreakSelect.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed', 'opacity-50');
            }

            containerBox.classList.remove(type === 'in' ? 'border-emerald-600' : 'border-indigo-600', type === 'in' ? 'bg-emerald-50/50' : 'bg-indigo-50/50');
            containerBox.classList.add('border-rose-500', 'bg-rose-50/30');
            badge.innerText = "削除理由";
        } else {
            timeInput.readOnly = false;
            timeInput.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed', 'opacity-50');
            
            // 👇 通常モードに戻ったら操作可能にする
            if (type === 'out' && autoBreakSelect) {
                autoBreakSelect.disabled = false;
                autoBreakSelect.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed', 'opacity-50');
            }

            containerBox.classList.remove('border-rose-500', 'bg-rose-50/30');
            containerBox.classList.add(type === 'in' ? 'border-emerald-600' : 'border-indigo-600', type === 'in' ? 'bg-emerald-50/50' : 'bg-indigo-50/50');
            badge.innerText = "必須";
        }
    }
</script>
</body>
</html>