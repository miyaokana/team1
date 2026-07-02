<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\AttendanceRequestController;
use App\Models\AttendanceRequest;

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
});


// 認証


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', [AuthController::class, 'logout']);

// 管理画面
Route::get('/admin/users', [AdminController::class, 'index']);
Route::get('/admin/users/create', [AdminController::class, 'create']);
Route::post('/admin/users/store', [AdminController::class, 'store']);
Route::get('/admin/users/delete/{id}', [AdminController::class, 'delete']);
Route::get('/admin/users/edit/{id}', [AdminController::class, 'edit']);
Route::post('/admin/users/update/{id}', [AdminController::class, 'update']);

//シフト
Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.shift');
Route::post('/shifts/store', [ShiftController::class, 'store'])->name('shifts.store');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    
    Route::post('/requests/{type}/{id}/status', [RequestController::class, 'updateStatus'])->name('requests.status');
});
// 各種申請
Route::get('/attendance-requests', [AttendanceRequestController::class, 'index'])->name('attedance_requests.index');
Route::post('/attendance-requests', [AttendanceRequestController::class, 'store'])->name('attedance_requests.store');
