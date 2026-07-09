<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>勤怠</title>

<link rel="stylesheet" href="{{ asset('css/layout.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">

</head>

<body>

@include('layouts.header')

<div class="layout">

@include('layouts.sidebar')


<div class="wrap">


<div class="notice-wide">

<div class="notice-left">

<span class="badge">
システム通知
</span>

@if(!empty($systemNotice))

{{ $systemNotice }}

@elseif(isset($notices) && $notices->count())

{{ $notices->first()->title }}

@else

現在お知らせはありません

@endif

</div>


<span class="confirm">
確認
</span>

</div>



@if(session('error'))

<div class="alert-danger">
{{ session('error') }}
</div>

@endif



@php

$wp=['日','月','火','水','木','金','土'][now()->dayOfWeek];

$isWorking=$status==='勤務中';

$isBreak=$status==='休憩中';

$notIn=$status==='未出勤';

@endphp




<div class="card">


<div class="shape s1"></div>
<div class="shape s2"></div>
<div class="shape s3"></div>


<div class="status-bar">

ただいま {{ $status }}

</div>



<div class="main">


<div class="left">


<div class="date">

{{ now()->format('Y年n月j日') }}
({{ $wp }})

</div>



<div class="clock" id="clock">

{{ now()->format('H:i') }}

<span class="sec">

{{ now()->format('s') }}

</span>

</div>




<div class="info">

    <div>
        勤務地：
        {{ $todayShift?->work_location ?? '未設定' }}
    </div>

    <div>
        シフト：
        @if($todayShift)
            {{ \Carbon\Carbon::parse($todayShift->start_time)->format('H:i') }}
            ～
            {{ \Carbon\Carbon::parse($todayShift->end_time)->format('H:i') }}
        @else
            未登録
        @endif
    </div>

    <div>
        休憩：
        @if($todayShift)

            @php
                $start = \Carbon\Carbon::parse($todayShift->start_time);
                $end = \Carbon\Carbon::parse($todayShift->end_time);

                if ($end->lt($start)) {
                    $end->addDay();
                }

                $hours = $start->diffInHours($end);

                if ($hours >= 8) {
                    $breakMinutes = 60;
                } elseif ($hours >= 7) {
                    $breakMinutes = 45;
                } elseif ($hours >= 6) {
                    $breakMinutes = 30;
                } else {
                    $breakMinutes = 0;
                }
            @endphp

            {{ $breakMinutes }}分

        @else
            --
        @endif
    </div>

</div>
</div> <!-- left終了 -->


<!-- 右側 -->

<div class="right">


<div class="location">

<span class="loc-label">
勤務地
</span>


<form action="{{ route('shift.location.update') }}" method="POST">

@csrf


<div class="loc-box">


<select name="work_location">


<option value="本社（出社）"
{{ ($todayShift?->work_location ?? '') === '本社（出社）' ? 'selected' : '' }}>
本社（出社）
</option>


<option value="研修（出社）"
{{ ($todayShift?->work_location ?? '') === '研修（出社）' ? 'selected' : '' }}>
研修（出社）
</option>


<option value="常駐先（出社）"
{{ ($todayShift?->work_location ?? '') === '常駐先（出社）' ? 'selected' : '' }}>
常駐先（出社）
</option>


</select>


<button type="submit" class="change-btn">
変更
</button>


</div>

</form>

</div>




<div class="punch-row">


<!-- 出勤 -->

<form action="{{ route('attendance.punch') }}" method="POST">

@csrf

<input type="hidden" name="type" value="check_in">


<button
class="big-btn in {{ !$notIn ? 'inactive':'' }}"
{{ $notIn ? '' : 'disabled' }}>

出勤

</button>


</form>




<!-- 退勤 -->

<form action="{{ route('attendance.punch') }}" method="POST">

@csrf

<input type="hidden" name="type" value="check_out">


<button
class="big-btn out {{ !$isWorking ? 'inactive':'' }}"
{{ $isWorking ? '' : 'disabled' }}>

退勤

</button>


</form>


</div>





<!-- 既定休憩 -->

<div class="toggle">

<span class="toggle-text">
既定の休憩を追加
</span>


<label class="switch">

<input 
type="checkbox"
name="auto_break"
value="1"
checked>

<span class="slider"></span>

</label>

</div>





<!-- 休憩 -->

<form action="{{ route('attendance.punch') }}" method="POST">

@csrf


@if($isBreak)

<input type="hidden" name="type" value="break_end">

<button class="sub-btn">

休憩終了

</button>


@else

<input type="hidden" name="type" value="break_start">


<button
class="sub-btn {{ !$isWorking?'inactive':'' }}"
{{ $isWorking?'':'disabled' }}>

休憩開始

</button>


@endif


</form>





<div class="bottom-btns">


<!-- ここを変更 -->

<button type="button" 
class="outline"
id="openRequest">

勤怠申請

</button>


<button class="outline">

打刻修正

</button>


</div>


</div>


</div>


</div>



<!-- 打刻履歴 -->

<div class="history-card">


<h3>
打刻履歴
</h3>


<div class="history-item">

<span class="tag">
出勤
</span>

{{ optional($attendance?->check_in)->format('Y-m-d H:i') ?? '-' }}

</div>



<div class="history-item">

<span class="tag">
退勤
</span>

{{ optional($attendance?->check_out)->format('Y-m-d H:i') ?? '-' }}

</div>


</div>

<!-- 勤怠申請モーダル -->

<div id="requestModal" class="request-modal">

<div class="request-modal-box">


<button type="button"
id="closeRequest"
class="request-close">
×
</button>



<h2>
各種申請（遅刻・早退・欠勤）
</h2>



<form action="{{ route('attendance_requests.store') }}"
method="POST"
enctype="multipart/form-data">

@csrf



<div class="form-row">

<label>
申請種別
</label>


<select name="type" id="requestType" required>

<option value="late">
遅刻
</option>

<option value="early_leave">
早退
</option>

<option value="absence">
欠勤
</option>

</select>

</div>




<div class="form-row">

<label>
対象日
</label>


<input type="date"
name="target_date"
required>

</div>





<div class="form-row"
id="requestTimeRow">

<label>
時刻
</label>


<input type="time"
name="request_time">

</div>




<div class="form-row">

<label>
理由
</label>


<textarea
name="reason"
rows="3"
required></textarea>


</div>





<div class="form-row">

<label>
添付ファイル
</label>


<input type="file"
name="attachment"
accept=".jpg,.jpeg,.png,.pdf">


</div>




<button type="submit"
class="btn-submit">

申請する

</button>



</form>


</div>

</div>






<script>


// 時計

setInterval(()=>{

const n=new Date();

const p=x=>String(x).padStart(2,'0');

const clock=document.getElementById('clock');

if(clock){

clock.innerHTML=

p(n.getHours())
+
':'
+
p(n.getMinutes())
+
'<span class="sec">'
+
p(n.getSeconds())
+
'</span>';

}

},1000);





// 勤怠申請モーダル

// 勤怠申請モーダル

const modal = document.getElementById('requestModal');
const open = document.getElementById('openRequest');
const close = document.getElementById('closeRequest');

open.onclick = () => {

    modal.style.display = 'block';

    // 背景スクロール禁止
    document.body.style.overflow = 'hidden';
};

close.onclick = () => {

    modal.style.display = 'none';

    // スクロール解除
    document.body.style.overflow = '';
};

window.onclick = (e) => {

    if (e.target === modal) {

        modal.style.display = 'none';

        // スクロール解除
        document.body.style.overflow = '';
    }
};






// 欠勤の場合は時刻を隠す

const type=document.getElementById('requestType');

const timeRow=document.getElementById('requestTimeRow');


function changeTime(){

if(type.value==='absence'){

timeRow.style.display='none';

}else{

timeRow.style.display='block';

}

}


type.addEventListener(
'change',
changeTime
);


changeTime();



</script>