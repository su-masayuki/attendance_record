<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * 管理者ダッシュボードを表示
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * 勤怠一覧を表示
     */
    public function attendanceList()
    {
        return view('admin_attendance_list');
    }

    /**
     * 管理者ログアウト
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
