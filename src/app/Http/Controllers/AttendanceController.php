<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\StampCorrection;
use App\Models\User; 
 
class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面を表示
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->latest()
            ->first();

        // ステータスを判定
        if (!$attendance) {
            $status = '勤務外'; // 出勤前
        } elseif ($attendance->clock_out) {
            $status = '退勤済';
        } elseif ($attendance->break_start && !$attendance->break_end) {
            $status = '休憩中';
        } else {
            $status = '出勤中';
        }

        return view('attendance', [
            'status' => $status,
            'date' => Carbon::now()->isoFormat('YYYY年M月D日(ddd)'),
            'time' => Carbon::now()->format('H:i')
        ]);
    }

    /**
     * 出勤処理
     */
    public function startWork()
    {
        Attendance::create([
            'user_id' => Auth::id(),
            'clock_in' => Carbon::now(),
        ]);

        return redirect()->route('attendance');
    }

    /**
     * 退勤処理
     */
    public function endWork()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if ($attendance) {
            $attendance->update(['clock_out' => Carbon::now()]);
        }

        return redirect()->route('attendance');
    }

    /**
     * 休憩開始処理
     */
    public function startBreak()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if ($attendance) {
            $attendance->update(['break_start' => Carbon::now()]);
        }

        return redirect()->route('attendance');
    }

    /**
     * 休憩終了処理
     */
    public function endBreak()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if ($attendance && $attendance->break_start) {
            $attendance->update(['break_end' => Carbon::now()]);
        }

        return redirect()->route('attendance');
    }

    /**
     * 勤怠一覧画面を表示
     */
    public function list()
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance_list', compact('attendances'));
    }

    public function show($id)
    {
            // 指定されたIDの勤怠情報を取得
            $attendance = Attendance::with('user')->findOrFail($id);

            // 修正申請を `user_id` と `target_date` で取得
            $requests = StampCorrection::where('user_id', $attendance->user_id)
                               ->where('target_date', $attendance->date)
                               ->get();

            return view('attendance_detail', compact('attendance', 'requests'));
    }

}