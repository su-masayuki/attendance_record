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
        $month = request()->query('month') 
            ? Carbon::createFromFormat('Y-m', request()->query('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        // 指定したスタッフの当月の勤怠情報を取得
        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->get();

        // ビュー名を正しいものに修正
        return view('admin_staff_attendance_list', compact('staff', 'attendances', 'month'));
    }

    public function show($id, $attendanceId)
    {
        $staff = User::findOrFail($id);
            $attendance = Attendance::with('breakTimes')->findOrFail($attendanceId);

        return view('admin_staff_attendance_detail', compact('staff', 'attendance'));
    }

    public function exportCsv($id)
    {
        $staff = User::findOrFail($id);
        $attendances = Attendance::where('user_id', $id)->with('breakTimes')->get();

        $csvData = [];
        foreach ($attendances as $attendance) {
            $csvData[] = [
                '日付' => $attendance->date,
                '出勤' => optional($attendance->clock_in)->format('H:i'),
                '退勤' => optional($attendance->clock_out)->format('H:i'),
            ];
        }

        $filename = 'attendance_' . $staff->id . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤']); // ヘッダー
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}