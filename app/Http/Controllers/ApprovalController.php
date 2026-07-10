<?php 

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Notice;
use App\Models\DakokuRequest;



class ApprovalController extends Controller
{
    // 承認一覧 (GET /approvals)
    public function index()
    {
        $companyId = Auth::user()->company_id;

        $companyName = Auth::user()->company->name;   // ← 追加

        // with('user') で申請者を一緒に読み込む。無いと申請ごとにユーザを問い合わせてＮ＋１で遅くなる
        $attendanceRequests = AttendanceRequest::with('user')
            ->whereHas('user', fn($q) => $q->where('company_id', $companyId))
            ->orderBy('created_at', 'desc')->get();

        $leaveRequests = LeaveRequest::with('user')
            ->whereHas('user', fn($q) => $q->where('company_id', $companyId))
            ->orderBy('created_at', 'desc')->get();

        $overtimeRequests = OvertimeRequest::with('user')
            ->whereHas('user', fn($q) => $q->where('company_id', $companyId))
            ->orderBy('created_at', 'desc')->get();

        
        $dakokuRequests = DakokuRequest::with('user')
        ->whereHas('user', fn($q) => $q->where('company_id', $companyId))
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->get();

        return view('approvals.index', compact(
            'attendanceRequests',
            'leaveRequests',
            'overtimeRequests',
            'dakokuRequests',
            'companyName'
        ));
    }

    // 承認・差し戻し(post /approvals/{type}/{id})
    public function update(Request $request, string $type, int $id)
    {
        // 差し戻し(rejected)の時だけ理由を必須にする。
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_comment' => 'required_if:status,rejected|nullable|string|max:1000',
        ], [
            'status.required' => 'ステータスが不正です。',
            'status.in' => 'ステータスが不正です。',
            'admin_comment.required_if' => '差し戻しの場合は理由を入力してください。',
        ]);

        // type から対象モデルを選ぶ
        $model = $this->resolveModel($type, $id);
        if (!$model) {
            return back()->with('error', '対象の申請が見つかりませんでした。');
        }

        // 対象申請が自社ユーザのものか確認(他社なら403)
        if (!$model->user || $model->user->company_id !== Auth::user()->company_id){
            abort(403, 'この申請を操作する権限はありません。');
        }

        // すでに処理済みのものは二重に承認・差し戻しさせない
        if ($model->status !== 'pending') {
            return back()->with('error', 'この申請は既に処理済みです。');
        }

        // 更新。承認者と承認日時も必ず記録する
        $model->status = $validated['status'];
        $model->admin_comment = $validated['admin_comment'] ?? null;
        $model->approver_id = Auth::id();
        $model->approved_at = now();
        $model->save();

         // 申請結果通知を作成
$typeLabel = match ($model->type) {
    'late' => '遅刻',
    'early_leave' => '早退',
    'absence' => '欠勤',
    default => '申請',
};

if ($validated['status'] === 'approved') {

    $message = "{$typeLabel}申請が承認されました。";

} else {

    $message = "{$typeLabel}申請が却下されました。";

    if (!empty($model->admin_comment)) {
        $message .= "\n\n理由：{$model->admin_comment}";
    }
    }

    Notice::create([
        'user_id' => $model->user_id,
        'title' => '申請結果通知',
        'message' => $message,
        'date' => today(),
        'is_read' => false,
    ]);

        if ($type === 'attendance'
            && $validated['status'] === 'approved'
            && $model->request_time
            && in_array($model->type, ['late', 'early_leave'], true)) {

            $attendance = Attendance::firstOrCreate([
                'user_id' => $model->user_id,
                'work_date' => $model->target_date->format('Y-m-d'),
            ]);

            $newTime = Carbon::parse(
                $model->target_date->format('Y-m-d') . ' ' . $model->request_time
            );

            // 遅刻・早退の時だけ反映(欠勤はここでは扱わない)
            if (in_array($model->type, ['late', 'early_leave'], true)){

                // 対象日のレコードを取得。無ければ作成(押し忘れの場合ないから)
                $attendance = Attendance::firstOrCreate([
                    'user_id' => $model -> user_id,
                    'work_date' => $model -> target_date -> format('Y-m-d',)
                ]);

                // 対象日 ＋ 申請時刻を結合してcheck_in にセット
                $newTime = Carbon::parse(
                    $model -> target_date -> format('Y-m-d') . ' ' . $model -> request_time
                );

                if ($model->type === 'late') {
                    $attendance->check_in = $newTime;
                } else {
                    $attendance->check_out = $newTime;
                }

                $attendance->save();
            }
        }

        $label = $validated['status'] === 'approved' ? '承認' : '差し戻し';
        return back()->with('status', "申請を{$label}しました。");
    }

    // type文字列から対象の申請モデルを取得する。
    private function resolveModel(string $type, int $id)
    {
        return match ($type) {
            'attendance' => AttendanceRequest::find($id),
            'leave' => LeaveRequest::find($id),
            'overtime' => OvertimeRequest::find($id),
            default => null,
        };
    }
}