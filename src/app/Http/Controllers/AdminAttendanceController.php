<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 日付を取得し、Carbon インスタンスに変換
        $selectedDate = Carbon::parse($request->input('date', now()->toDateString()));

        // 勤怠データを取得
        $attendances = Attendance::whereDate('created_at', $selectedDate->toDateString())->get();

        return view('admin_attendance_list', compact('attendances', 'selectedDate'));
    }

    public function show($id)
    {
        // 指定されたIDの勤怠情報を取得
        $attendance = Attendance::with('user')->findOrFail($id);

        // `date` カラムが null の場合は `created_at` を使用
        $attendanceDate = $attendance->date ? Carbon::parse($attendance->date)->format('Y/m/d') : Carbon::parse($attendance->created_at)->format('Y/m/d');

        return view('admin_attendance_detail', compact('attendance', 'attendanceDate'));
    }
}
