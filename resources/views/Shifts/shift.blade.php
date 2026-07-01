<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>シフト表</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="p-4 md:p-8 bg-slate-50 text-slate-800 antialiased">

    <div class="max-w-5xl mx-auto bg-white p-4 md:p-8 rounded-2xl shadow-sm border border-slate-100">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 border-b border-slate-100 pb-5">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">シフト表</h1>
                <p class="text-xs md:text-sm text-slate-500 mt-1">ユーザーごとの月間シフトの確認・登録</p>
            </div>

            <form method="GET" action="{{ route('shifts.shift') }}" id="viewForm" class="flex flex-wrap items-center gap-2.5">
                <select name="user_id" onchange="document.getElementById('viewForm').submit()" class="border border-slate-200 px-3 py-2 rounded-xl bg-white text-sm font-medium text-slate-700 shadow-xs focus:outline-none focus:border-blue-500">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $selectedUser && $selectedUser->id == $user->id ? 'selected' : '' }}>
                            {{ $user->user_name }}
                        </option>
                    @endforeach
                </select>
                <input type="month" name="month" value="{{ $currentMonth->format('Y-m') }}" onchange="document.getElementById('viewForm').submit()" class="border border-slate-200 px-3 py-2 rounded-xl bg-white text-sm font-medium text-slate-700 shadow-xs focus:outline-none focus:border-blue-500">
            </form>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(!$selectedUser)
            <div class="p-12 text-center text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                先に社員データを登録してください。
            </div>
        @else
            <div class="mb-4 px-1 flex items-baseline gap-1.5">
                <span class="text-xl font-bold text-blue-600">【{{ $selectedUser->user_name }} さん】</span>
                <span class="text-sm md:text-base font-semibold text-slate-600">の{{ $currentMonth->format('Y年m月') }}シフト</span>
            </div>

            <form method="POST" action="{{ route('shifts.store_bulk') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">

                <div class="mb-6 p-4 bg-blue-50/50 border border-blue-100 rounded-2xl flex flex-col gap-4">
    
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-blue-100/60 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2.5 py-1 rounded-md">一括設定</span>
                            <p class="text-xs text-slate-600 font-medium">下にチェックを入れた日付にまとめて適用します</p>
                        </div>
        
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleAllDates(true)" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                                全選択
                            </button>
                            <button type="button" onclick="toggleWorkdayDates()" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                                土日祝除くすべて
                            </button>
                            <button type="button" onclick="toggleAllDates(false)" class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition-colors cursor-pointer">
                                選択解除
                            </button>
                        </div>
                    </div>
    
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <div class="flex items-center gap-3 bg-white p-2 rounded-xl border border-slate-200/60 shadow-2xs w-full sm:w-auto justify-between sm:justify-start">
                            <div class="flex items-center gap-1 text-sm">
                                <span class="text-xs font-bold text-slate-500">勤務先：</span>
                                <select name="work_location" class="border border-slate-200 rounded-lg p-1 text-xs font-medium bg-slate-50 focus:outline-none focus:border-blue-500">
                                    <option value="（研修）研修所" {{ old('work_location') === '（研修）研修所' ? 'selected' : '' }}>（研修）研修所</option>
                                    <option value="本社（出社）" {{ old('work_location') === '本社（出社）' ? 'selected' : '' }}>本社（出社）</option>
                                    <option value="本社（在宅）" {{ old('work_location') === '本社（在宅）' ? 'selected' : '' }}>本社（在宅）</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-1 text-sm">
                                <select name="bulk_start_hour" class="border border-slate-200 rounded-lg p-1 text-xs font-mono bg-slate-50 focus:outline-none focus:border-blue-500">
                                    @for ($hour = 9; $hour <= 23; $hour++)
                                        @foreach (['00', '30'] as $min)
                                            @php $time = sprintf('%02d:%s', $hour, $min); @endphp
                                            <option value="{{ $time }}" {{ old('bulk_start_hour') === $time ? 'selected' : '' }}>{{ $time }}</option>
                                        @endforeach
                                    @endfor
                                    <option value="24:00" {{ old('bulk_start_hour') === '24:00' ? 'selected' : '' }}>24:00</option>
                                </select>

                                <span class="text-slate-400">~</span>

                                <select name="bulk_end_hour" class="border border-slate-200 rounded-lg p-1 text-xs font-mono bg-slate-50 focus:outline-none focus:border-blue-500">
                                    @for ($hour = 9; $hour <= 23; $hour++)
                                        @foreach (['00', '30'] as $min)
                                            @php $time = sprintf('%02d:%s', $hour, $min); @endphp
                                            <option value="{{ $time }}+0" {{ old('bulk_end_hour', '17:30') === $time || old('bulk_end_hour') === $time.'+0' ? 'selected' : '' }}>{{ $time }}</option>
                                        @endforeach
                                    @endfor
                                    <option value="24:00+0" {{ old('bulk_end_hour') === '24:00+0' ? 'selected' : '' }}>24:00</option>

                                    @for ($hour = 1; $hour <= 12; $hour++)
                                        @foreach (['00', '30'] as $min)
                                            @php 
                                                $time = sprintf('%02d:%s', $hour, $min);
                                                $val = $time . '+1'; // 例: "09:00+1" 
                                                $disp = sprintf('翌%02d:%s', $hour, $min); // 画面表示は「翌09:00」
                                            @endphp
                                            <option value="{{ $val }}" {{ old('bulk_end_hour') === $val ? 'selected' : '' }}>{{ $disp }}</option>
                                        @endforeach
                                    @endfor
                                </select>
                            </div>
                            
                            <div class="flex items-center gap-1.5">
                                <button type="submit" name="action" value="register" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors shadow-xs cursor-pointer">
                                    選択日に一括登録
                                </button>
                                <button type="submit" name="action" value="delete" class="px-3 py-1.5 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-bold rounded-lg transition-colors border border-transparent hover:border-rose-200 cursor-pointer">
                                    一括削除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-xs bg-white">
                    <div class="grid grid-cols-7 bg-slate-50 text-center text-xs font-bold border-b border-slate-200 py-2.5 text-slate-500">
                        <button type="button" onclick="toggleDayOfWeek(0)" class="text-rose-500 hover:bg-rose-50 py-1 rounded cursor-pointer font-bold">日</button>
                        <button type="button" onclick="toggleDayOfWeek(1)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">月</button>
                        <button type="button" onclick="toggleDayOfWeek(2)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">火</button>
                        <button type="button" onclick="toggleDayOfWeek(3)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">水</button>
                        <button type="button" onclick="toggleDayOfWeek(4)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">木</button>
                        <button type="button" onclick="toggleDayOfWeek(5)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">金</button>
                        <button type="button" onclick="toggleDayOfWeek(6)" class="text-blue-500 hover:bg-blue-50 py-1 rounded cursor-pointer font-bold">土</button>
                    </div>

                    <div class="grid grid-cols-7 bg-slate-200 gap-[1px]">
                        
                        @for ($i = 0; $i < $startOfWeek; $i++)
                            <div class="bg-slate-50/60 min-h-[120px]"></div>
                        @endfor

                        @foreach($dates as $date)
                            @php
                                $formattedDate = $date->format('Y-m-d');

                                // 1. シフト（出勤）データがあるか判定
                                $shift = null;
                                if ($shifts && $shifts->count() > 0) {
                                    $shift = $shifts->first(function($s) use ($formattedDate) {
                                        if (!$s || !isset($s->shift_date)) return false;

                                        $dbDate = $s->shift_date instanceof \Carbon\Carbon 
                                            ? $s->shift_date->format('Y-m-d') 
                                            : substr($s->shift_date, 0, 10);
            
                                        return $dbDate === $formattedDate;
                                    });
                                }

                                // 2. 祝日データに含まれているか判定
                                $isHoliday = is_array($holidays) && in_array($formattedDate, $holidays); 

                                // 3. 日付の文字色を決定 (日曜日 or 祝日 = 赤 / 土曜日 = 青 / 平日 = 黒)
                                if ($date->isSunday() || $isHoliday) {
                                    $dateColor = 'text-rose-600';
                                } elseif ($date->isSaturday()) {
                                    $dateColor = 'text-blue-600';
                                } else {
                                    $dateColor = 'text-slate-700';
                                }

                                // 4. マスの背景色を決定 (出勤 = 薄緑 / 未登録かつ祝日・日曜日 = 薄赤 / 土曜日 = 薄青 / 通常 = 白)
                                // 💡 競合を防ぐため、hover時の色も背景色に合わせて最適化します
                                if ($shift) {
                                    $boxBg = 'bg-emerald-100 hover:bg-emerald-200/70'; 
                                } elseif ($date->isSunday() || $isHoliday) {
                                    $boxBg = 'bg-rose-100 hover:bg-rose-200/70';    
                                } elseif ($date->isSaturday()) {
                                    $boxBg = 'bg-blue-50 hover:bg-blue-100/70';      
                                } else {
                                    $boxBg = 'bg-white hover:bg-slate-50';       
                                }

                                $dayNum = $date->dayOfWeek;
                            @endphp
            
                            <div class="{{ $boxBg }} min-h-[120px] p-2.5 flex flex-col justify-between relative group transition-colors js-calendar-cell cursor-pointer">
                                <div class="flex justify-between items-start">
                                    <input type="checkbox" name="selected_dates[]" value="{{ $formattedDate }}" data-dow="{{ $dayNum }}" data-is-holiday="{{ $isHoliday ? 'true' : 'false' }}" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer mt-0.5">
                    
                                    <span class="text-xs font-bold {{ $dateColor }} flex flex-col items-end">
                                        {{ $date->format('j') }}
                                    </span>
                                </div>

                                <div class="mt-2 w-full text-center">
                                    @if($shift)
                                        <div class="flex flex-col items-center gap-1 py-1">
                                            <span class="inline-flex px-1.5 py-0.5 bg-emerald-600 text-white text-[9px] font-bold rounded">
                                                出勤
                                            </span>
                                            @if(!empty($shift->work_location))
                                                <span class="text-[10px] text-blue-700 font-bold bg-blue-50 px-1 rounded">
                                                    {{ $shift->work_location }}
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-slate-700 font-mono font-bold">
                                                {{ Carbon\Carbon::parse($shift->start_time)->format('H:i') }}-{{ Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 block py-2">未登録</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @php $totalCells = $startOfWeek + count($dates); @endphp
                        @while ($totalCells % 7 != 0)
                            <div class="bg-slate-50/60 min-h-[120px]"></div>
                            @php $totalCells++; @endphp
                        @endwhile
                    </div>
                </div>
            </form>
            @php
                $totalDays = $shifts->count();
                $totalHours = 0;
                foreach($shifts as $s) {
                    $start = \Carbon\Carbon::parse($s->start_time);
                    $end = \Carbon\Carbon::parse($s->end_time);
                    
                    // 💡 修正：退勤時間が、出勤時間より前の時刻なら「日跨ぎ（翌日）」と判定して1日足す
                    if ($end->lt($start)) {
                        $end->addDay();
                    }
                    
                    $diffInHours = $start->diffInHours($end);
                    if ($diffInHours >= 6) { $diffInHours -= 1; } // 休憩時間の控除ロジック
                    $totalHours += $diffInHours;
                }
            @endphp
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 flex flex-col justify-center">
                    <span class="text-xs font-medium text-slate-500 mb-1">当月合計出勤日数</span>
                    <div class="flex items-baseline gap-1"><span class="text-2xl font-bold text-slate-900">{{ $totalDays }}</span><span class="text-sm text-slate-600 font-medium">日</span></div>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 flex flex-col justify-center">
                    <span class="text-xs font-medium text-slate-500 mb-1">当月合計勤務時間</span>
                    <div class="flex items-baseline gap-1"><span class="text-2xl font-bold text-blue-600">{{ $totalHours }}</span><span class="text-sm text-slate-600 font-medium">時間</span></div>
                </div>
            </div>
        @endif
    </div>

    <script>
        // 既存の関数（曜日ごとの個別選択）
        function toggleDayOfWeek(dow) {
            const checkboxes = document.querySelectorAll(`input[data-dow="${dow}"]`);
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
        }

        // すべての曜日にチェックを入れる / 外す
        function toggleAllDates(status) {
            const checkboxes = document.querySelectorAll('input[name="selected_dates[]"]');
            checkboxes.forEach(cb => cb.checked = status);
        }

        function toggleWorkdayDates() {
            // 一度すべてのチェックをクリア
            toggleAllDates(false);
        
            const checkboxes = document.querySelectorAll('input[name="selected_dates[]"]');
            checkboxes.forEach(cb => {
                const dow = parseInt(cb.getAttribute('data-dow'));
                const isHoliday = cb.getAttribute('data-is-holiday') === 'true';
            
                // 月(1)〜金(5) であり、かつ祝日（isHoliday）ではない場合のみチェック
                if (dow >= 1 && dow <= 5 && !isHoliday) {
                    cb.checked = true;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const cells = document.querySelectorAll('.js-calendar-cell');
            
            cells.forEach(cell => {
                cell.addEventListener('click', (e) => {
                    const checkbox = cell.querySelector('input[name="selected_dates[]"]');
                    if (!checkbox) return;

                    // チェックボックス自体や、将来的に追加されるかもしれないリンク、
                    // フォーム要素（selectやbutton等）のクリック時は何もしない（重複発火を防ぐ）
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'BUTTON' || e.target.tagName === 'A') {
                        return;
                    }

                    // チェック状態を反転
                    checkbox.checked = !checkbox.checked;
                });
            });
        });
    </script>

</body>
</html>