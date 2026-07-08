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

return view('dashboard',[
'attendance'=>$attendance,
'status'=>$this->resolveStatus($attendance),
'workMinutes'=>$this->workMinutes($attendance),
'breakMinutes'=>$this->breakMinutes($attendance),
'notices'=>$notices,
'todayShift'=>$todayShift,
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