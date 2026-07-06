<!DOCTYPE html>


<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>シフト表</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
</head>


<body class="p-4 md:p-8 bg-slate-50 text-slate-800 antialiased">

    @include('layouts.header')

<div class="layout">

    @include('layouts.sidebar')

    <div class="wrap">



    <div class="max-w-5xl mx-auto bg-white p-4 md:p-8 rounded-2xl shadow-sm border border-rose-300">
        
        <div class="flex flex-col items-center text-center gap-2 mb-6 border-b border-slate-100 pb-5">
            <h1 class="text-4xl font-bold text-slate-900 tracking-tight">シフト一覧</h1>
            <p class="text-base md:text-lg text-slate-500 mt-1">月間シフトの確認・登録・修正</p>
        </div>

        {{-- 「月間切り替え」 --}}
        <div class="mb-6 px-1 flex flex-col sm:flex-row sm:items-center justify-center gap-3 bg-slate-50 p-5 rounded-2xl">
            <form method="GET" action="{{ route('shifts.shift') }}" id="viewForm" class="flex items-center justify-center gap-2 w-full">
                @if(request('user_id'))
                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                @endif
                
                <div class="flex items-center bg-white border-2 border-black rounded-2xl shadow-2xs overflow-hidden h-14 min-w-[280px]">
                    {{-- ◀ 前の月ボタン --}}
                    <button type="button" onclick="changeMonth(-1)" class="w-14 h-full hover:bg-slate-50 text-black transition-colors cursor-pointer font-bold border-r-2 border-black text-xl flex items-center justify-center">
                        &lt;
                    </button>
                    
                    {{-- 月選択インプット --}}
                    <input type="month" name="month" id="monthInput" value="{{ $currentMonth->format('Y-m') }}" onchange="document.getElementById('viewForm').submit()" class="px-6 py-2 bg-transparent text-xl font-bold text-slate-800 focus:outline-none cursor-pointer tracking-wide text-center">
                    
                    {{-- ▶ 次の月ボタン --}}
                    <button type="button" onclick="changeMonth(1)" class="w-14 h-full hover:bg-slate-50 text-black transition-colors cursor-pointer font-bold border-l-2 border-black text-xl flex items-center justify-center">
                        &gt;
                    </button>
                </div>
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
            <span class="text-xl font-bold text-rose-400">【{{ $selectedUser->user_name }} さん】</span>
        </div>

        <form method="POST" action="{{ route('shifts.store_bulk') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
            <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">

            <div class="mb-6 p-6 bg-blue-50/50 border-2 border-rose-400 rounded-2xl flex flex-col gap-4">
    
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-blue-100/60 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-blue-700 bg-blue-100 px-3.5 py-1.5 rounded-md">一括設定</span>
                    </div>
        
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleAllDates(true)" class="px-3.5 py-1.5 bg-white hover:bg-slate-100 text-black text-sm font-semibold rounded-lg border border-black shadow-2xs transition-colors cursor-pointer">
                            全選択
                        </button>
                        <button type="button" onclick="toggleWorkdayDates()" class="px-3.5 py-1.5 bg-white hover:bg-slate-100 text-black text-sm font-semibold rounded-lg border border-black shadow-2xs transition-colors cursor-pointer">
                            土日祝除くすべて
                        </button>
                        <button type="button" onclick="toggleAllDates(false)" class="px-3.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-black text-sm font-semibold rounded-lg border border-black shadow-2xs transition-colors cursor-pointer">
                            選択解除
                        </button>
                    </div>
                    <p class="text-sm text-black font-medium">勤務先、勤務時間を設定してからチェックを入れた日付にまとめて適用します</p>
                </div>
    
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <div class="flex items-center gap-3 bg-white p-4 rounded-xl border border-black shadow-2xs w-full sm:w-auto justify-between sm:justify-start">
                        <div class="flex items-center gap-1 text-sm">
                            <span class="text-base font-bold text-black">勤務先：</span>
                            <select name="work_location_base" class="border border-black rounded-lg p-2 text-base text-black font-medium bg-slate-50 focus:outline-none focus:border-blue-500">
                                <option value="本社" {{ old('work_location_base') === '本社' ? 'selected' : '' }}>本社</option>
                                <option value="研修" {{ old('work_location_base') === '研修' ? 'selected' : '' }}>研修</option>
                                <option value="常駐先" {{ old('work_location_base') === '常駐先' ? 'selected' : '' }}>常駐先</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-1 text-sm">
                            <select name="work_style" class="border border-black rounded-lg p-2 text-base text-black font-medium bg-slate-50 focus:outline-none focus:border-blue-500">
                                <option value="出社" {{ old('work_style') === '出社' ? 'selected' : '' }}>出社</option>
                                <option value="在宅" {{ old('work_style') === '在宅' ? 'selected' : '' }}>在宅</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-1 text-sm">
                            <span class="text-base font-bold text-black">勤務時間：</span>
                            <select name="bulk_start_hour" class="border border-black rounded-lg p-2 text-lg text-black font-mono bg-slate-50 focus:outline-none focus:border-blue-500">
                                @for ($hour = 9; $hour <= 23; $hour++)
                                    @foreach (['00', '30'] as $min)
                                        @php $time = sprintf('%02d:%s', $hour, $min); @endphp
                                        <option value="{{ $time }}" {{ old('bulk_start_hour', '09:00') === $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                @endfor
                                <option value="24:00" {{ old('bulk_start_hour') === '24:00' ? 'selected' : '' }}>24:00</option>
                            </select>

                            <span class="text-sm font-bold text-black">～</span>

                            <select name="bulk_end_hour" class="border border-black rounded-lg p-2 text-lg text-black font-mono bg-slate-50 focus:outline-none focus:border-blue-500">
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
                            <button type="submit" name="action" value="delete" onclick="return confirm('選択した日付のシフトを削除します。よろしいですか？')" class="px-3 py-1.5 bg-slate-200 hover:bg-rose-300 hover:text-rose-600 text-black text-xs font-bold rounded-lg transition-colors border border-black hover:border-rose-200 cursor-pointer">
                                一括削除
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $mappedShifts = [];
                if ($shifts && $shifts->count() > 0) {
                    foreach ($shifts as $s) {
                        if (!$s || !isset($s->shift_date)) continue;
                        $dateKey = $s->shift_date instanceof \Carbon\Carbon 
                            ? $s->shift_date->format('Y-m-d') 
                            : substr($s->shift_date, 0, 10);
                        $mappedShifts[$dateKey] = $s;
                    }
                }
            @endphp

            <div class="border-2 border-rose-400 rounded-2xl overflow-hidden shadow-xs bg-white">
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
                                $blankBg = 'bg-blue-200 hover:bg-blue-300';
                            } else { // 平日
                                $blankBg = 'bg-white hover:bg-slate-200/60';
                            }
                        @endphp
                        <div class="{{ $blankBg }} min-h-[120px] transition-colors"></div>
                    @endfor

                    @foreach($dates as $date)
                        @php
                            $formattedDate = $date->format('Y-m-d');
                            $shift = $mappedShifts[$formattedDate] ?? null;
                            $isHoliday = is_array($holidays) && in_array($formattedDate, $holidays); 

                            if ($date->isSunday() || $isHoliday) {
                                $dateColor = 'text-rose-600';
                            } elseif ($date->isSaturday()) {
                                $dateColor = 'text-blue-600';
                            } else {
                                $dateColor = 'text-green-600';
                            }

                            // 💡 カレンダーのマス全体の背景色判定（シフトがある日は在宅・出社で色分け）
                            if ($shift) {
                                $locationStr = $shift->work_location ?? '';
                                if (str_contains($locationStr, '在宅')) {
                                    $boxBg = 'bg-emerald-50 hover:bg-emerald-100/80'; // 🏠在宅の日は薄い緑
                                } else {
                                    $boxBg = 'bg-emerald-50 hover:bg-emerald-100/80';  // 🏢出社の日は薄い黄
                                }
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
                                    @php
                                        $locationStr = $shift->work_location ?? '';
                                        if (str_contains($locationStr, '在宅')) {
                                            $badgeClass = 'bg-emerald-500 text-white';
                                            $locTextClass = 'text-emerald-700 bg-white';
                                        } else {
                                            $badgeClass = 'bg-emerald-500 text-white';
                                            $locTextClass = 'text-emerald-700 bg-white';
                                        }
                                    @endphp
                                    <div class="flex flex-col items-center gap-1.5 py-1 w-full">
                                        <span class="inline-flex px-2 py-0.5 {{ $badgeClass }} text-xs font-extrabold rounded shadow-2xs tracking-wider">
                                            出勤
                                        </span>
                                        @if(!empty($shift->work_location))
                                            <span class="text-xs md:text-sm {{ $locTextClass }} font-extrabold px-1.5 py-0.5 rounded text-center block max-w-full truncate">
                                                {{ $shift->work_location }}
                                            </span>
                                        @endif
                                        <span class="text-lg md:text-lg text-slate-800 font-mono font-extrabold tracking-tighter whitespace-nowrap">
                                            {{ Carbon\Carbon::parse($shift->start_time)->format('H:i') }}-{{ Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-[15px] text-black block py-2 select-none tracking-tighter">未登録</span>
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
            $totalDays = $shifts ? $shifts->count() : 0;
            $totalHours = 0;
            if ($shifts) {
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
            }
        @endphp
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="bg-slate-50 p-4 rounded-xl border-2 border-rose-400 flex flex-col justify-center">
                <span class="text-xs font-medium text-black mb-1">当月合計出勤日数</span>
                <div class="flex items-baseline gap-1"><span class="text-2xl font-bold text-blue-600">{{ $totalDays }}</span><span class="text-sm text-black font-medium">日</span></div>
            </div>
            <div class="bg-slate-50 p-4 rounded-xl border-2 border-rose-400 flex flex-col justify-center">
                <span class="text-xs font-medium text-black mb-1">当月合計勤務時間</span>
                <div class="flex items-baseline gap-1"><span class="text-2xl font-bold text-blue-600">{{ $totalHours }}</span><span class="text-sm text-black font-medium">時間</span></div>
            </div>
        </div>

        {{-- 登録済みシフトの詳細一覧テーブル --}}
        <div class="mt-8">
            <h2 class="text-xl font-bold text-slate-900 mb-3 px-1">📅 登録済みシフト詳細一覧</h2>
            <div class="border-2 border-rose-400 rounded-2xl overflow-hidden bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-rose-100 border-b-2 border-rose-300 text-base font-bold text-slate-700">
                                <th class="p-3 border-r border-rose-300 text-center w-28">日付</th>
                                <th class="p-3 border-r border-rose-300">勤務地</th>
                                <th class="p-3 border-r border-rose-300 text-center w-36">出勤時間</th>
                                <th class="p-3 border-r border-rose-300 text-center w-36">退勤時間</th>
                                <th class="p-3 border-r border-rose-300 text-center w-24">休憩時間</th>
                                <th class="p-3 text-center w-28">勤務時間</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-medium text-slate-800">
                            @if($shifts && $shifts->count() > 0)
                                @php
                                    $sortedShifts = $shifts->sortBy('shift_date');
                                    $currentWeekId = null; 
                                @endphp

                                @foreach($sortedShifts as $s)
                                    @php
                                        $dateObj = \Carbon\Carbon::parse($s->shift_date);
                                        $weeks = ['日', '月', '火', '水', '木', '金', '土'];
                                        $dow = $weeks[$dateObj->dayOfWeek];
                                        
                                        // 💡 テーブル一覧の「出社/在宅」による背景色・テキスト色の動的判定
                                        $locationStrTable = $s->work_location ?? '';
                                        if (str_contains($locationStrTable, '在宅')) {
                                            $rowBgStyle = 'bg-emerald-50/60 hover:bg-emerald-100/70';
                                            $locationBadgeStyle = 'text-emerald-700';
                                        } else {
                                            $rowBgStyle = $loop->odd ? 'bg-white hover:bg-amber-50/40' : 'bg-amber-50/20 hover:bg-amber-100/40';
                                            $locationBadgeStyle = 'text-amber-700';
                                        }

                                        if ($dateObj->isSunday()) { $bgAndColor = 'text-rose-600 bg-rose-50/50'; }
                                        elseif ($dateObj->isSaturday()) { $bgAndColor = 'text-blue-600 bg-blue-50/50'; }
                                        else { $bgAndColor = 'text-slate-800'; }

                                        $start = \Carbon\Carbon::parse($s->start_time);
                                        $end = \Carbon\Carbon::parse($s->end_time);
                                        $isNextDay = $end->lt($start);
                                        
                                        if ($isNextDay) {
                                            $endForCalc = $end->copy()->addDay();
                                            $endDisp = '翌 ' . $end->format('H:i');
                                        } else {
                                            $endForCalc = $end;
                                            $endDisp = $end->format('H:i');
                                        }

                                        $diffInHours = $start->diffInHours($endForCalc);
                                        $restHours = ($diffInHours >= 6) ? 1 : 0;
                                        $workHours = $diffInHours - $restHours;

                                        $thisWeekId = $dateObj->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d');
                                        $isNewWeek = ($currentWeekId !== null && $currentWeekId !== $thisWeekId);
                                        $currentWeekId = $thisWeekId;
                                    @endphp

                                    @if($isNewWeek)
                                        <tr class="bg-rose-100 h-4 border-y border-rose-200">
                                            <td colspan="6" class="p-0 text-[1px] leading-none select-none">&nbsp;</td>
                                        </tr>
                                    @endif

                                    <tr class="transition-colors border-b border-rose-300">
                                        {{-- 日付 --}}
                                        <td class="p-3 border-r border-rose-300 text-center font-bold text-base {{ $bgAndColor }}">
                                            {{ $dateObj->format('m/d') }}（{{ $dow }}）
                                        </td>
                                        {{-- 勤務地 --}}
                                        <td class="p-3 border-r border-rose-300 font-extrabold text-base text-slate-800">
                                            {{ $s->work_location ?? '未設定' }}
                                        </td>
                                        {{-- 出勤時間 --}}
                                        <td class="p-3 border-r border-rose-300 text-center font-mono font-bold text-slate-700 text-lg">
                                            {{ $start->format('H:i') }}
                                        </td>
                                        {{-- 退勤時間 --}}
                                        <td class="p-3 border-r border-rose-300 text-center font-mono font-bold text-lg {{ $isNextDay ? 'text-amber-600' : 'text-slate-700' }}">
                                            {{ $endDisp }}
                                        </td>
                                        {{-- 休憩時間 --}}
                                        <td class="p-3 border-r border-rose-300 text-center font-semibold text-slate-500 text-base">
                                            {{ $restHours }} 時間
                                        </td>
                                        {{-- 勤務時間 --}}
                                        <td class="p-3 text-center font-black text-blue-600 bg-amber-50/50 text-base">
                                            {{ $workHours }} 時間
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 font-medium bg-slate-50">
                                        今月の登録済みシフトはありません。
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
</div>
</div>

    <script>
        function changeMonth(offset) {
            const input = document.getElementById('monthInput');
            if (!input.value) return;

            let [year, month] = input.value.split('-').map(Number);
            let date = new Date(year, month - 1 + offset, 1);
            
            let newYear = date.getFullYear();
            let newMonth = String(date.getMonth() + 1).padStart(2, '0');
            
            input.value = `${newYear}-${newMonth}`;
            document.getElementById('viewForm').submit();
        }

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
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.tagName === 'OPTION') {
                        return;
                    }
                    checkbox.checked = !checkbox.checked;
                });
            });
        });
    </script>
</body>
</html>