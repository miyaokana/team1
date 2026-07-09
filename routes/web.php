<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\PasswordResetController; // 💡こちらに統一するで！
use App\Http\Controllers\AdminRequestController; 
use App\Models\AttendanceRequest;
use App\Models\OvertimeRequest;
use App\Models\Notice;
use App\Http\Controllers\DakokuRequestController;

// トップ
Route::get('/', function () {
    return redirect('/login');
});

// ログイン必須。未ログインなら /loginに飛ばされる
Route::middleware('auth')->group(function () {
    // ダッシュボード画面（打刻）
    Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');

    // 打刻 POST /attendance/punch
    Route::post('/attendance/punch', [AttendanceController::class, 'punch'])->name('attendance.punch');

    // 打刻履歴
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');

    // 打刻申請
    Route::get('/attendance-requests', [AttendanceRequestController::class, 'index'])->name('attendance_requests.index');
    Route::post('/attendance-requests', [AttendanceRequestController::class, 'store'])->name('attendance_requests.store');

    // 残業申請
    Route::get('/overtime-requests', [OvertimeRequestController::class, 'index'])->name('overtime_requests.index');
    Route::post('/overtime-requests', [OvertimeRequestController::class, 'store'])->name('overtime_requests.store');

    // 有給申請
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave_requests.store');

    // 打刻修正
    Route::get('/dakoku/request', [DakokuRequestController::class, 'createUserView'])->name('dakoku.request.create');
    Route::post('/dakoku/request', [DakokuRequestController::class, 'storeApplication'])->name('dakoku.request.store');

    // 添付ファイル
    Route::get('/attendance-requests/{attendanceRequest}/attachment',
        [AttendanceRequestController::class, 'downloadAttachment'])
        ->name('attendance_requests.attachment');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/approvals', [\App\Http\Controllers\ApprovalController::class, 'index'])
        ->name('approvals.index');
        
    Route::post('/approvals/{type}/{id}', [\App\Http\Controllers\ApprovalController::class, 'update'])
        ->name('approvals.update');

    // 管理画面
    Route::get('/admin/users', [AdminController::class, 'index']);
    Route::get('/admin/users/create', [AdminController::class, 'create']);
    Route::post('/admin/users/store', [AdminController::class, 'store']);
    Route::post('/admin/users/import', [AdminController::class, 'import']);
    Route::post('/admin/users/store-multiple', [AdminController::class, 'storeMultiple']);
    Route::get('/admin/users/delete/{id}', [AdminController::class, 'delete']);
    Route::get('/admin/users/edit/{id}', [AdminController::class, 'edit']);
    Route::post('/admin/users/update/{id}', [AdminController::class, 'update']);
    Route::get('/admin/users/{id}/attendance', [AdminController::class, 'attendance']);
    Route::get('/admin/attendance/{id}/edit', [AdminController::class, 'editAttendance']);
    Route::post('/admin/attendance/{id}/update', [AdminController::class, 'updateAttendance']);
    Route::get('/admin/users/requests', [DakokuRequestController::class, 'adminIndex'])->name('admin.dakoku.requests.index');
    Route::post('/admin/users/requests/{id}/approve', [DakokuRequestController::class, 'adminApprove'])->name('admin.dakoku.requests.approve');
});

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// 認証
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');


// シフト  
Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.shift');
Route::post('/shifts/store', [ShiftController::class, 'store'])->name('shifts.store');
Route::post('/shifts/bulk', [ShiftController::class, 'storeBulk'])->name('shifts.store_bulk');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/notices', function () {
    $notices = Notice::latest()->get();
    return view('notices.index', ['notices' => $notices]);
})->name('notices.index');


// ==========================================================
// 💡 パスワードリセット関連（すべて PasswordResetController に綺麗に統一！）
// ==========================================================

// ① パスワード再設定メールのアドレス入力画面を表示（GET）
Route::get('/forgot-password', [PasswordResetController::class, 'showForm'])->name('password.request');

// ② パスワード再設定用のURLメールを送信する処理（POST）
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

// ③ メールのURLから飛んでくる、新しいパスワードの入力画面（GET）
Route::get('/reset-password/{email}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');

// ④ 実際にパスワードをDBにアップデートする処理（POST）
Route::post('/reset-password', [PasswordResetController::class, 'updatePassword'])->name('password.update');

Route::post(
    '/shift/location',
    [AttendanceController::class, 'updateLocation']
)->name('shift.location.update');

