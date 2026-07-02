<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>シフト表</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="p-4 md:p-8 bg-slate-50 text-slate-800 antialiased">

    <div class="max-w-5xl mx-auto bg-white p-4 md:p-8 rounded-2xl shadow-sm border border-black">
        
        <div class="flex flex-col items-center text-center gap-2 mb-6 border-b border-slate-100 pb-5">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">シフト一覧</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">月間シフトの確認・登録</p>
        </div>

        {{-- 「月間切り替え」 --}}
        <div class="mb-5 px-1 flex flex-col sm:flex-row sm:items-center justify-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <form method="GET" action="{{ route('shifts.shift') }}" id="viewForm" class="flex items-center justify-center gap-2 w-full">
                <input type="month" name="month" value="{{ $currentMonth->format('Y-m') }}" onchange="document.getElementById('viewForm').submit()" class="border border-slate-200 px-3 py-1.5 rounded-xl bg-white text-sm font-semibold text-slate-700 shadow-2xs focus:outline-none focus:border-blue-500 cursor-pointer">
            </form>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
                @if(session('error'))
                    <div>{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="mb-4 px-1 flex items-baseline gap-1.5">
            <span class="text-xl font-bold text-blue-600">【{{ $selectedUser->user_name }} さん】</span>
            <span class="text-sm md:text-base font-semibold text-slate-600">の{{ $currentMonth->format('Y年m月') }}シフト</span>
        </div>

        <form method="POST" action="{{ route('shifts.store_bulk') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
            <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">

            <div class="mb-6 p-4 bg-blue-50/50 border-2 border-black rounded-2xl flex flex-col gap-4">
    
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-blue-100/60 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2.5 py-1 rounded-md">一括設定</span>
                        <p class="text-xs text-slate-600 font-medium">下にチェックを入れた日付にまとめて適用します</p>
                    </div>
        
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleAllDates(true)" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg border border-slate-400 shadow-2xs transition-colors cursor-pointer">
                            全選択
                        </button>
                        <button type="button" onclick="toggleWorkdayDates()" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg border border-slate-400 shadow-2xs transition-colors cursor-pointer">
                            土日祝除くすべて
                        </button>
                        <button type="button" onclick="toggleAllDates(false)" class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-lg border border-slate-400 shadow-2xs transition-colors cursor-pointer">
                            選択解除
                        </button>
                    </div>
                </div>
    
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <div class="flex items-center gap-3 bg-white p-2 rounded-xl border border-slate-400 shadow-2xs w-full sm:w-auto justify-between sm:justify-start">
                        <div class="flex items-center gap-1 text-sm">
                            <span class="text-xs font-bold text-black">勤務先：</span>
                            <select name="work_location" class="border border-slate-400 rounded-lg p-1 text-xs font-medium bg-slate-50 focus:outline-none focus:border-blue-500">
                                <option value="本社（出社）" {{ old('work_location') === '本社（出社）' ? 'selected' : '' }}>本社（出社）</option>
                                <option value="本社（在宅）" {{ old('work_location') === '本社（在宅）' ? 'selected' : '' }}>本社（在宅）</option>
                                <option value="研修（研修所）" {{ old('work_location') === '研修（研修所）' ? 'selected' : '' }}>研修（研修所）</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-1 text-sm">
                            <select name="bulk_start_hour" class="border border-slate-400 rounded-lg p-1 text-xs font-mono bg-slate-50 focus:outline-none focus:border-blue-500">
                                @for ($hour = 9; $hour <= 23; $hour++)
                                    @foreach (['00', '30'] as $min)
                                        @php $time = sprintf('%02d:%s', $hour, $min); @endphp
                                        <option value="{{ $time }}" {{ old('bulk_start_hour', '09:00') === $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                @endfor
                                <option value="24:00" {{ old('bulk_start_hour') === '24:00' ? 'selected' : '' }}>24:00</option>
                            </select>

                            <span class="text-slate-400">~</span>

                            <select name="bulk_end_hour" class="border border-slate-400 rounded-lg p-1 text-xs font-mono bg-slate-50 focus:outline-none focus:border-blue-500">
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
                                            $val = $time . '+1'; 
                                            $disp = sprintf('翌%02d:%s', $hour, $min); 
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
                            <button type="submit" name="action" value="delete" onclick="return confirm('選択した日付のシフトを削除します。よろしいですか？')" class="px-3 py-1.5 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-bold rounded-lg transition-colors border border-slate-400 hover:border-rose-200 cursor-pointer">
                                一括削除
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-2 border-black rounded-2xl overflow-hidden shadow-xs bg-white">
                <div class="grid grid-cols-7 bg-slate-50 text-center text-lg font-bold border-b-3 border-slate-400 py-2.5 text-green-600">
                    <button type="button" onclick="toggleDayOfWeek(0)" class="text-rose-500 hover:bg-rose-50 py-1 rounded cursor-pointer font-bold">日</button>
                    <button type="button" onclick="toggleDayOfWeek(1)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">月</button>
                    <button type="button" onclick="toggleDayOfWeek(2)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">火</button>
                    <button type="button" onclick="toggleDayOfWeek(3)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">水</button>
                    <button type="button" onclick="toggleDayOfWeek(4)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">木</button>
                    <button type="button" onclick="toggleDayOfWeek(5)" class="hover:bg-slate-200/60 py-1 rounded cursor-pointer font-bold">金</button>
                    <button type="button" onclick="toggleDayOfWeek(6)" class="text-blue-500 hover:bg-blue-50 py-1 rounded cursor-pointer font-bold">土</button>
                </div>

                <div class="grid grid-cols-7 bg-slate-400 gap-[3px]">
                        
                    @for ($i = 0; $i < $startOfWeek; $i++)
                        @php
                            if ($i === 0) { // 日曜日
                                $blankBg = 'bg-rose-100 hover:bg-rose-200/70';
                            } elseif ($i === 6) { // 土曜日
                                $blankBg = 'bg-blue-200 hover:bg-blue-300s';
                            } else { // 平日
                                $blankBg = 'bg-white hover:bg-slate-200/60';
                            }
                        @endphp
                        <div class="{{ $blankBg }} min-h-[120px] transition-colors"></div>
                    @endfor

                    @foreach($dates as $date)
                        @php
                            $formattedDate = $date->format('Y-m-d');

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

                            $isHoliday = is_array($holidays) && in_array($formattedDate, $holidays); 

                            if ($date->isSunday() || $isHoliday) {
                                $dateColor = 'text-rose-600';
                            } elseif ($date->isSaturday()) {
                                $dateColor = 'text-blue-600';
                            } else {
                                $dateColor = 'text-green-600';
                            }

                            if ($shift) {
                                $boxBg = 'bg-emerald-100 hover:bg-emerald-200/70'; 
                            } elseif ($date->isSunday() || $isHoliday) {
                                $boxBg = 'bg-rose-100 hover:bg-rose-200/70';    
                            } elseif ($date->isSaturday()) {
                                $boxBg = 'bg-blue-200 hover:bg-blue-300';      
                            } else {
                                $boxBg = 'bg-white hover:bg-slate-200/60';       
                            }

                            $dayNum = $date->dayOfWeek;
                                
                            $isChecked = is_array(old('selected_dates')) && in_array($formattedDate, old('selected_dates'));
                        @endphp
            
                        <div class="{{ $boxBg }} min-h-[120px] p-2.5 flex flex-col justify-between relative group transition-colors js-calendar-cell cursor-pointer">
                            <div class="flex justify-between items-start">
                                <input type="checkbox" name="selected_dates[]" value="{{ $formattedDate }}" data-dow="{{ $dayNum }}" data-is-holiday="{{ $isHoliday ? 'true' : 'false' }}" {{ $isChecked ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer mt-0.5">
                    
                                <span class="text-lg md:text-xl font-extrabold {{ $dateColor }} flex flex-col items-end tracking-tight">
                                    {{ $date->format('j') }}
                                </span>
                            </div>

                            <div class="mt-2 w-full text-center">
                                @if($shift)
                                    <div class="flex flex-col items-center gap-1.5 py-1 w-full">
                                        <span class="inline-flex px-2 py-0.5 bg-emerald-600 text-white text-xs font-extrabold rounded shadow-2xs tracking-wider">
                                            出勤
                                        </span>
                                        @if(!empty($shift->work_location))
                                            <span class="text-xs md:text-sm text-blue-800 font-extrabold bg-blue-50/80 px-1.5 py-0.5 rounded text-center block max-w-full truncate">
                                                {{ $shift->work_location }}
                                            </span>
                                        @endif
                                        <span class="text-lg md:text-lg text-slate-800 font-mono font-extrabold tracking-tighter whitespace-nowrap">
                                            {{ Carbon\Carbon::parse($shift->start_time)->format('H:i') }}-{{ Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-[15px] text-black block py-2">未登録</span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @php $totalCells = $startOfWeek + count($dates); @endphp
                    @while ($totalCells % 7 != 0)
                        @php
                            $currentDow = $totalCells % 7;
                            if ($currentDow === 0) { // 日曜日
                                $blankBg = 'bg-rose-100 hover:bg-rose-200/70';
                            } elseif ($currentDow === 6) { // 土曜日
                                $blankBg = 'bg-blue-200 hover:bg-blue-300';
                            } else { // 平日
                                $blankBg = 'bg-white hover:bg-slate-200/60';
                            }
                        @endphp
                        <div class="{{ $blankBg }} min-h-[120px] transition-colors"></div>
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
                    
                if ($end->lt($start)) {
                    $end->addDay();
                }
                    
                $diffInHours = $start->diffInHours($end);
                if ($diffInHours >= 6) { $diffInHours -= 1; } 
                $totalHours += $diffInHours;
            }
        @endphp
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-400 flex flex-col justify-center">
                <span class="text-xs font-medium text-black mb-1">当月合計出勤日数</span>
                <div class="flex items-baseline gap-1"><span class="text-2xl font-bold text-slate-900">{{ $totalDays }}</span><span class="text-sm text-black font-medium">日</span></div>
            </div>
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-400 flex flex-col justify-center">
                <span class="text-xs font-medium text-black mb-1">当月合計勤務時間</span>
                <div class="flex items-baseline gap-1"><span class="text-2xl font-bold text-blue-600">{{ $totalHours }}</span><span class="text-sm text-black font-medium">時間</span></div>
            </div>
        </div>
    </div>

    <script>
        function toggleDayOfWeek(dow) {
            const checkboxes = document.querySelectorAll(`input[data-dow="${dow}"]`);
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
        }

        function toggleAllDates(status) {
            const checkboxes = document.querySelectorAll('input[name="selected_dates[]"]');
            checkboxes.forEach(cb => cb.checked = status);
        }

        function toggleWorkdayDates() {
            toggleAllDates(false);
            const checkboxes = document.querySelectorAll('input[name="selected_dates[]"]');
            checkboxes.forEach(cb => {
                const dow = parseInt(cb.getAttribute('data-dow'));
                const isHoliday = cb.getAttribute('data-is-holiday') === 'true';
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
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'BUTTON' || e.target.tagName === 'A') {
                        return;
                    }
                    checkbox.checked = !checkbox.checked;
                });
            });
        });
    </script>
</body>
</html>