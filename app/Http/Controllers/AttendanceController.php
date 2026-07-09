<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Notice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
public function dashboard()
{
$attendance=Attendance::where('user_id',Auth::id())->where('work_date',today())->first();
$notices=Notice::latest()->get();
$todayShift=Shift::where('user_id',Auth::id())->whereDate('shift_date',today())->first();

$systemNotice = null;

if (!$todayShift) {

    $systemNotice = '本日のシフトは登録されていません。';

} else {

    $startTime = Carbon::parse($todayShift->start_time);
    $endTime   = Carbon::parse($todayShift->end_time);

    // 未出勤
    if (!$attendance?->check_in) {

        if (now()->lt($startTime)) {

            $systemNotice =
                '本日の勤務予定：'
                .$startTime->format('H:i')
                .'～'
                .$endTime->format('H:i');

        } else {

            $lateMinutes = (int) floor(
                $startTime->diffInSeconds(now()) / 60
            );

            $systemNotice =
                "出勤打刻をしてください（{$lateMinutes}分経過）";
        }
    }

    // 休憩中
    elseif (
        $attendance->break_start &&
        !$attendance->break_end
    ) {

        $breakMinutes = (int) floor(
            $attendance->break_start
                ->diffInSeconds(now()) / 60
        );

        if ($breakMinutes >= 60) {

            $systemNotice =
                "休憩開始から{$breakMinutes}分経過しています";

        } else {

            $systemNotice = '休憩中です';
        }
    }

    // 退勤済み
    elseif ($attendance->check_out) {

        if ($attendance->check_out->lt($endTime)) {

            $earlyMinutes = (int) floor(
                $attendance->check_out
                    ->diffInSeconds($endTime) / 60
            );

            $systemNotice =
                "本日は{$earlyMinutes}分の早退が発生しています";

        } elseif ($attendance->check_out->gt($endTime)) {

            $overMinutes = (int) floor(
                $endTime
                    ->diffInSeconds($attendance->check_out) / 60
            );

            $systemNotice =
                "本日は{$overMinutes}分の残業でした";

        } else {

            $systemNotice =
                '本日の勤務は終了しました';
        }
    }

    // 勤務中
    else {

        $remainMinutes = (int) floor(
            now()->diffInSeconds($endTime, false) / 60
        );

        if ($remainMinutes > 0 && $remainMinutes <= 30) {

            $systemNotice =
                "退勤予定まであと{$remainMinutes}分です";

        } elseif ($remainMinutes <= 0) {

            $overMinutes = abs($remainMinutes);

            if ($overMinutes >= 30) {

                $systemNotice =
                    "残業が{$overMinutes}分発生しています";

            } else {

                $systemNotice =
                    '退勤予定時刻を過ぎています';
            }
        } else {

            $systemNotice =
                '本日もよろしくお願いします';
        }
    }
}

return view('dashboard',[
'attendance'=>$attendance,
'status'=>$this->resolveStatus($attendance),
'workMinutes'=>$this->workMinutes($attendance),
'breakMinutes'=>$this->breakMinutes($attendance),
'notices'=>$notices,
'todayShift'=>$todayShift,
'systemNotice'=>$systemNotice,
]);
}

public function punch(Request $request)
{
$validated=$request->validate([
'type'=>'required|in:check_in,check_out,break_start,break_end',
]);

$type=$validated['type'];

$attendance=Attendance::firstOrCreate([
'user_id'=>Auth::id(),
'work_date'=>today(),
]);

if($type==='check_in'){
$todayShift=Shift::where('user_id',Auth::id())->whereDate('shift_date',today())->first();

if(!$todayShift){
return back()->with('error','本日のシフトが登録されていません。');
}
}

if($error=$this->validatePunch($type,$attendance)){
return back()->with('error',$error);
}

if($type==='check_out'){

$attendance->check_out=now();

if($request->boolean('auto_break')){

$workMinutes=$attendance->check_in->diffInMinutes($attendance->check_out);

if($workMinutes>=480){
$attendance->break_minutes=60;
}elseif($workMinutes>=420){
$attendance->break_minutes=45;
}elseif($workMinutes>=360){
$attendance->break_minutes=30;
}else{
$attendance->break_minutes=0;
}

}else{

$attendance->break_minutes=0;

}

$attendance->save();

}else{

$attendance->{$type}=now();
$attendance->save();

}

$labels=[
'check_in'=>'出勤',
'check_out'=>'退勤',
'break_start'=>'休憩開始',
'break_end'=>'休憩終了',
];

return back()->with('status',$labels[$type].'を記録しました（'.now()->format('H:i').'）');
}

public function history()
{
$userId=Auth::id();
$from=today()->subDays(30);
$to=today();

$records=Attendance::where('user_id',$userId)->where('work_date','>=',$from)->get()->keyBy(fn($a)=>$a->work_date->format('Y-m-d'));

$shifts=Shift::where('user_id',$userId)->where('shift_date','>=',$from->format('Y-m-d'))->get()->keyBy(fn($s)=>Carbon::parse($s->shift_date)->format('Y-m-d'));

$approvedAbsences=\App\Models\AttendanceRequest::where('user_id',$userId)->where('type','absence')->where('status','approved')->where('target_date','>=',$from->format('Y-m-d'))->get()->keyBy(fn($r)=>Carbon::parse($r->target_date)->format('Y-m-d'));

$rows=collect();

for($date=$to->copy();$date->gte($from);$date->subDay()){

$key=$date->format('Y-m-d');

$attendance=$records->get($key);
$shift=$shifts->get($key);
$isApprovedAbsence=$approvedAbsences->has($key);

if(!$attendance&&!$shift&&!$isApprovedAbsence){
continue;
}

$rows->push([
'date'=>$date->copy(),
'record'=>$attendance,
'shift'=>$shift,
'workMinutes'=>$this->workMinutes($attendance),
'state'=>$this->dayState($attendance,$shift,$isApprovedAbsence),
'diff'=>$this->calcDiff($attendance,$shift),
]);

}

return view('attendance.history',['rows'=>$rows]);
}
private function calcDiff(?Attendance $a,$shift):array
{
$late=null;
$early=null;
$overtime=null;

if(!$shift||!$a){
return['late'=>null,'early'=>null,'overtime'=>null];
}

$toMin=fn($dt)=>Carbon::parse($dt)->startOfMinute();

if($a->check_in){
$planStart=$toMin($shift->start_time);
$realIn=$toMin($a->check_in);
if($realIn->gt($planStart)){
$late=$planStart->diffInMinutes($realIn);
}
}

if($a->check_out){
$planEnd=$toMin($shift->end_time);
$realOut=$toMin($a->check_out);

if($realOut->lt($planEnd)){
$early=$planEnd->diffInMinutes($realOut);
}elseif($realOut->gt($planEnd)){
$overtime=$realOut->diffInMinutes($planEnd);
}
}

return[
'late'=>$late,
'early'=>$early,
'overtime'=>$overtime,
];
}

private function dayState(?Attendance $a,$shift,bool $isApprovedAbsence=false):string
{
if(!$a||!$a->check_in){

if($isApprovedAbsence){
return'承認済み欠勤';
}

if($shift){
return'無断欠勤';
}

return'---';
}

if($a->check_out)return'退勤済み';
if($a->break_start&&!$a->break_end)return'休憩中';
return'勤務中';
}

private function validatePunch(string $type,Attendance $a):?string
{
return match($type){
'check_in'=>$a->check_in?'既に出勤打刻済みです。':null,

'check_out'=>match(true){
!$a->check_in=>'先に出勤打刻をしてください。',
(bool)$a->check_out=>'既に退勤打刻済みです。',
$a->break_start&&!$a->break_end=>'休憩終了を打刻してから退勤してください。',
default=>null,
},

'break_start'=>match(true){
!$a->check_in=>'先に出勤打刻をしてください。',
(bool)$a->check_out=>'退勤後は休憩できません。',
(bool)$a->break_start=>'既に休憩開始を打刻済みです。',
default=>null,
},

'break_end'=>match(true){
!$a->break_start=>'先に休憩開始を打刻してください。',
(bool)$a->break_end=>'既に休憩終了を打刻済みです。',
default=>null,
},

default=>'不明な打刻種別です。',
};
}

private function resolveStatus(?Attendance $a):string
{
if(!$a||!$a->check_in)return'未出勤';
if($a->check_out)return'退勤済み';
if($a->break_start&&!$a->break_end)return'休憩中';
return'勤務中';
}

private function workMinutes(?Attendance $a):?int
{
if(!$a||!$a->check_in||!$a->check_out){
return null;
}

$minutes=$a->check_in->diffInMinutes($a->check_out);

if(($a->break_minutes??0)>0){

$minutes-=$a->break_minutes;

}elseif($a->break_start&&$a->break_end){

$minutes-=$a->break_start->diffInMinutes($a->break_end);

}

return max(0,$minutes);
}

private function breakMinutes(?Attendance $a):?int
{
if(!$a){
return null;
}

if(($a->break_minutes??0)>0){
return $a->break_minutes;
}

if($a->break_start&&$a->break_end){
return $a->break_start->diffInMinutes($a->break_end);
}

return 0;
}

public function updateLocation(Request $request)
{
$request->validate([
'work_location'=>'required|in:本社（出社）,研修（出社）,常駐先（出社）',
]);

$shift=Shift::where('user_id',Auth::id())->whereDate('shift_date',today())->first();

if($shift){
$shift->update([
'work_location'=>$request->work_location,
]);
}

return redirect()->route('dashboard')->with('success','勤務地を変更しました');
}
}