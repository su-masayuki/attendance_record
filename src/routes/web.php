<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAttendanceController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// 一般ユーザー用ルート
Route::middleware('auth:web')->group(function () {
    Route::get('/', fn() => redirect('/attendance'));
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::post('/attendance/start', [AttendanceController::class, 'startWork'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'endWork'])->name('attendance.end');
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak'])->name('attendance.break.start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak'])->name('attendance.break.end');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
    Route::get('/stamp_correction_request/list', [StampCorrectionController::class, 'index'])->name('stamp_correction_request.list');
});

// 一般ユーザーのログイン
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// 管理者ログイン
Route::middleware(['guest:admin'])->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
});

// 認証済み管理者のルート
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/request/list', [AdminRequestController::class, 'index'])->name('admin.request.list');
});

Route::middleware(['web'])->post('/logout', function (Request $request) {
    $user = Auth::user();

    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // 管理者なら管理者ログイン画面へ、一般ユーザーなら通常ログイン画面へ
    return redirect($user && $user->is_admin ? '/admin/login' : '/login');
})->name('logout');