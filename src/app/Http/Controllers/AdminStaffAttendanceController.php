<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffAttendanceController extends Controller
{
    public function index($id)
    {
        $staff = User::findOrFail($id);
        $month = Carbon::now()->format('Y-m'); // 現在の年月

        // 指定したスタッフの当月の勤怠情報を取得
        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', Carbon::now()->year)
            ->whereMonth('date', Carbon::now()->month)
            ->get();

        return view('admin.staff_admin_attendance_list', compact('staff', 'attendances', 'month'));
    }
}