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
        $month = request('month');
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::with(['breakTimes', 'stampCorrection'])
            ->where('user_id', $staff->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date')
            ->get();

        $csvHeader = ['日付', '出勤', '退勤', '休憩合計', '勤務時間', '詳細'];
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($attendances, $csvHeader) {
            $stream = fopen('php://output', 'w');
            fputs($stream, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($stream, $csvHeader);

            foreach ($attendances as $attendance) {
                $clockIn = optional(Carbon::parse($attendance->clock_in))->format('H:i') ?? '-';
                $clockOut = optional(Carbon::parse($attendance->clock_out))->format('H:i') ?? '-';

                $totalBreakMinutes = $attendance->breakTimes->reduce(function ($carry, $break) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);
                    return $carry + ($end && $start ? $end->diffInMinutes($start) : 0);
                }, 0);

                $workMinutes = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $start = Carbon::parse($attendance->clock_in);
                    $end = Carbon::parse($attendance->clock_out);
                    $workMinutes = max(0, $end->diffInMinutes($start) - $totalBreakMinutes);
                }

                $row = [
                    Carbon::parse($attendance->date)->format('Y-m-d'),
                    $clockIn,
                    $clockOut,
                    $totalBreakMinutes > 0 ? \Carbon\CarbonInterval::minutes($totalBreakMinutes)->cascade()->format('%H:%I') : '00:00',
                    $workMinutes > 0 ? \Carbon\CarbonInterval::minutes($workMinutes)->cascade()->format('%H:%I') : '00:00',
                    optional($attendance->stampCorrection)->reason ?? ''
                ];
                fputcsv($stream, $row);
            }

            fclose($stream);
        });

        $fileName = $staff->name . '_attendance_' . $month . '.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}