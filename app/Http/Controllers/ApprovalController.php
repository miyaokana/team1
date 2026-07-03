<?php 

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    // 承認一覧 (GET /approvals)
    public function index()
    {
        // with('user') で申請者を一緒に読み込む。無いと申請ごとにユーザを問い合わせてＮ＋１で遅くなる
        $attendanceRequests = AttendanceRequest::with('user')
            ->orderBy('created_at', 'desc')->get();
        $leaveRequests = LeaveRequest::with('user')
            ->orderBy('created_at', 'desc')->get();
        $overtimeRequests = OvertimeRequest::with('user')
            ->orderBy('created_at', 'desc')->get();

        return view('approvals.index', compact(
            'attendanceRequests',
            'leaveRequests',
            'overtimeRequests',
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

        if ($type === 'attendance'
            && $validated['status'] === 'approved'
            && $model->type === 'late'
            && $model->request_time) {

            // 対象日のレコードを取得。無ければ作成(押し忘れの場合ないから)
            $attendance = Attendance::firstOrCreate([
                'user_id' => $model -> user_id,
                'work_date' => $model -> target_date -> format('Y-m-d',)
            ]);

            // 対象日 ＋ 申請時刻を結合してcheck_in にセット
            $newCheckIn = Carbon::parse(
                $model -> target_date -> format('Y-m-d') . ' ' . $model -> request_time
            );
            $attendance->check_in = $newCheckIn;
            $attendance->save();
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