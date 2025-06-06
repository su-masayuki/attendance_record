<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminStaffAttendanceController;
use App\Http\Controllers\AdminAttendanceController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AdminLoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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
    Route::post('/attendance/{id}/request', [AttendanceController::class, 'submitCorrectionRequest'])->name('attendance.request');
});

// 一般ユーザーのログイン
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// 管理者ログイン
Route::middleware(['guest:admin'])->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'store']);
});

// 認証済み管理者のルート
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');
    Route::post('/attendance/{id}/update', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/staff/attendance/{id}', [AdminStaffAttendanceController::class, 'index'])->name('admin.attendance.staff');
    Route::get('/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'index'])->name('admin.attendance.staff_list');
    Route::get('/request/list', [AdminRequestController::class, 'index'])->name('admin.request.list');
    Route::get('/attendance/staff/{id}/csv', [AdminStaffAttendanceController::class, 'exportCsv'])->name('admin.attendance.csv');
});

// 勤怠修正申請承認関連
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceController::class, 'showApproval'])->name('admin.attendance.approval');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceController::class, 'approve'])->name('admin.attendance.approve');
});

// 共通ルート（一般ユーザー・管理者）
Route::middleware(['auth:web,admin'])->group(function () {
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
    Route::get('/stamp_correction_request/list', [StampCorrectionController::class, 'index'])->name('stamp_correction_request.list');
});

// ログアウト
Route::middleware(['web'])->post('/logout', function (Request $request) {
    $user = Auth::user();
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect($user && $user->is_admin ? '/admin/login' : '/login');
})->name('logout');

// メール認証関連
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::post('/email/verification-notification', function () {
    request()->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');