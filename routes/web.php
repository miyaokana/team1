<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

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