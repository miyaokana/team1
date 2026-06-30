<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ShiftController;

// トップ
Route::get('/', function () {
    return redirect('/login');
});

// 認証
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/dashboard', [AuthController::class, 'dashboard']);
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