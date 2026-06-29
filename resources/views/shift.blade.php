<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>シフト一覧</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        /* 縦横スクロール時にヘッダーと社員名を固定するCSS */
        .table-container { overflow-x: auto; max-height: 600px; }
        th.sticky-top { position: sticky; top: 0; background: #f3f4f6; z-index: 10; }
        th.sticky-left, td.sticky-left { position: sticky; left: 0; background: #fff; z-index: 5; }
        th.sticky-both { position: sticky; top: 0; left: 0; background: #e5e7eb; z-index: 20; }
    </style>
</head>
<body class="p-8 bg-gray-50">

    <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-sm">
        <h1 class="text-2xl font-bold mb-6">シフト一覧（{{ $currentMonth->format('Y年m月') }}）</h1>

        <form method="GET" action="{{ route('shifts.index') }}" class="mb-6 flex items-center gap-2">
            <input type="month" name="month" value="{{ $currentMonth->format('Y-m') }}" class="border p-2 rounded">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">表示</button>
        </form>

        <div class="table-container border rounded-lg">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr>
                        <th class="p-3 border-b border-r text-center sticky-both w-32">社員名</th>
                        @foreach($dates as $date)
                            @php 
                                $bgClass = $date->isSaturday() ? 'text-blue-600' : ($date->isSunday() ? 'text-red-600' : 'text-gray-700');
                            @endphp
                            <th class="p-2 border-b border-r text-center sticky-top min-w-[60px] {{ $bgClass }}">
                                {{ $date->format('d') }}<br>
                                <span class="text-xs">{{ $date->isoFormat('dd') }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="p-3 border-b border-r font-medium sticky-left shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">{{ $user->name }}</td>
                            @foreach($dates as $date)
                                @php
                                    $shift = $user->shifts->firstWhere('date', $date->format('Y-m-d'));
                                @endphp
                                <td class="p-2 border-b border-r text-center hover:bg-gray-50">
                                    <span class="px-2 py-1 rounded text-xs {{ $shift && $shift->status === '休み' ? 'bg-red-100 text-red-800' : ($shift ? 'bg-green-100 text-green-800' : 'text-gray-400') }}">
                                        {{ $shift ? $shift->status : '-' }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>