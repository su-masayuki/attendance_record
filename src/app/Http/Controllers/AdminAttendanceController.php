<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\StampCorrection;
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

    public function showApproval($id)
    {
        $correction = StampCorrection::with('user', 'attendance')->findOrFail($id);
        $attendance = $correction->attendance;

        return view('admin_attendance_approval', [
            'correction' => $correction,
            'attendance' => $attendance, // ← 必ず渡す
        ]);
    }

    public function approve($attendance_correct_request)
    {
        $correction = \App\Models\StampCorrection::with('attendance')->findOrFail($attendance_correct_request);
        $attendance = $correction->attendance;

        if (!$attendance) {
            return redirect()->back()->with('error', '該当する勤怠データが見つかりませんでした。');
        }

        // 勤怠情報を修正申請の内容で更新
        $attendance->clock_in = $correction->clock_in;
        $attendance->clock_out = $correction->clock_out;
        // $attendance->note = $correction->reason;
        $attendance->save();

        // break_timesがある場合、削除してから再登録
        $attendance->breakTimes()->delete();

        $decodedBreaks = json_decode($correction->breaks, true); // ← 修正

        if (is_array($decodedBreaks)) {
            foreach ($decodedBreaks as $break) {
                $attendance->breakTimes()->create([
                    'break_start' => $break['start'] ?? null,
                    'break_end' => $break['end'] ?? null,
                 ]);
            }
        }

        // ステータスを更新
        $correction->status = '承認済み';
        $correction->save();

        return redirect()->route('stamp_correction_request.list')->with('success', '修正申請を承認しました。');
    }
}
