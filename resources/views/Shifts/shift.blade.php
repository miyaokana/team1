<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>シフト表</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="p-8 bg-gray-50 text-gray-800">

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-md">
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 border-b pb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">シフト表</h1>
                <p class="text-sm text-gray-500">ユーザーごとの月間シフトの確認・登録</p>
            </div>

            <form method="GET" action="{{ route('shifts.shift') }}" class="flex flex-wrap items-center gap-3">
                <select name="user_id" onchange="this.form.submit()" class="border p-2 rounded-lg bg-white shadow-xs text-sm">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $selectedUser && $selectedUser->id == $user->id ? 'selected' : '' }}>
                            {{ $user->user_name }}
                        </option>
                    @endforeach
                </select>

                <input type="month" name="month" value="{{ $currentMonth->format('Y-m') }}" onchange="this.form.submit()" class="border p-2 rounded-lg bg-white shadow-xs text-sm">
            </form>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if(!$selectedUser)
            <div class="p-8 text-center text-gray-500 bg-gray-50 rounded-lg">
                社員データがありません。先に社員データを登録してください。
            </div>
        @else
            <div class="mb-4 px-1">
                <span class="text-lg font-semibold text-blue-600">【{{ $selectedUser->user_name }}さん】</span>
                <span class="text-lg font-medium text-gray-700">の{{ $currentMonth->format('Y年m月') }}シフト</span>
            </div>

            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-xs">
                <div class="grid grid-cols-7 bg-gray-100 text-center text-xs font-bold border-b border-gray-200 py-3">
                    <div class="text-red-600">日</div>
                    <div>月</div>
                    <div>火</div>
                    <div>水</div>
                    <div>木</div>
                    <div>金</div>
                    <div class="text-blue-600">土</div>
                </div>

                <div class="grid grid-cols-7 bg-gray-200 gap-[1px]">
                    
                    @for ($i = 0; $i < $startOfWeek; $i++)
                        <div class="bg-gray-50 min-h-[120px]"></div>
                    @endfor

                    @foreach($dates as $date)
                        @php
                            $shift = $shifts->firstWhere('shift_date', $date->format('Y-m-d'));
                            $dateColor = $date->isSunday() ? 'text-red-600' : ($date->isSaturday() ? 'text-blue-600' : 'text-gray-700');
                            $boxBg = $shift ? 'bg-green-50' : 'bg-white';
                        @endphp
                        
                        <div class="{{ $boxBg }} min-h-[130px] p-2 flex flex-col justify-between transition-colors hover:bg-gray-50">
                            <span class="text-xs font-semibold {{ $dateColor }}">
                                {{ $date->format('j') }}
                            </span>

                            <div class="mt-1 text-center">
                                @if($shift)
                                    <div class="flex flex-col items-center gap-1 py-1">
                                        <span class="px-2 py-0.5 bg-green-500 text-white text-[10px] font-bold rounded-md shadow-xs">
                                            出勤
                                        </span>
                                        <span class="text-[11px] text-gray-700 font-mono font-bold mt-0.5">
                                            {{ Carbon\Carbon::parse($shift->start_time)->format('H:i') }}-{{ Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                        </span>
                            
                                        <form method="POST" action="{{ route('shifts.store') }}" class="m-0 mt-1">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                                            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 hover:underline cursor-pointer">
                                                取消
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('shifts.store') }}" class="m-0 flex flex-col gap-1 items-center">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                                        <input type="hidden" name="action" value="register">
                            
                                        <div class="flex items-center gap-0.5 text-[10px]">
                                            <select name="start_hour" class="border rounded bg-white p-0.5 text-gray-700">
                                                <option value="09:00">09:00</option>
                                                <option value="09:30">09:30</option>
                                                <option value="10:00">10:00</option>
                                                <option value="13:00">13:00</option>
                                                <option value="17:00">17:00</option>
                                            </select>
                                            <span class="text-gray-400">~</span>
                                            <select name="end_hour" class="border rounded bg-white p-0.5 text-gray-700">
                                                <option value="17:30">17:30</option>
                                                <option value="18:00">18:00</option>
                                                <option value="18:30">18:30</option>
                                                <option value="19:00">19:00</option>
                                                <option value="22:00">22:00</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="w-full py-0.5 mt-1 text-[10px] bg-blue-50 text-blue-600 rounded border border-blue-200 hover:bg-blue-600 hover:text-white transition-all cursor-pointer font-medium shadow-xs">
                                            登録
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @php $totalCells = $startOfWeek + count($dates); @endphp
                    @while ($totalCells % 7 != 0)
                        <div class="bg-gray-50 min-h-[120px]"></div>
                        @php $totalCells++; @endphp
                    @endwhile
                    
                </div>
            </div>

            @php
                $totalDays = $shifts->count();
                $totalHours = 0;
                foreach($shifts as $s) {
                    $start = \Carbon\Carbon::parse($s->start_time);
                    $end = \Carbon\Carbon::parse($s->end_time);
                    $diffInHours = $start->diffInHours($end);
                    if ($diffInHours >= 6) {
                        $diffInHours -= 1; 
                    }
                    $totalHours += $diffInHours;
                }
            @endphp

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 flex flex-col justify-center">
                    <span class="text-xs font-medium text-gray-500 mb-1">当月合計出勤日数</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold text-gray-900">{{ $totalDays }}</span>
                        <span class="text-sm text-gray-600 font-medium">日</span>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 flex flex-col justify-center">
                    <span class="text-xs font-medium text-gray-500 mb-1">当月合計勤務時間（休憩控除済）</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold text-blue-600">{{ $totalHours }}</span>
                        <span class="text-sm text-gray-600 font-medium">時間</span>
                    </div>
                </div>
            </div>

        @endif
    </div>

</body>
</html>