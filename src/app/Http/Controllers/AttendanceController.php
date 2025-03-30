<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\StampCorrection;
use App\Models\User; 
use App\Models\BreakTime;
 
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
            $status = '勤務外';
        } elseif ($attendance->clock_out) {
            $status = '退勤済';
        } else {
            // 最後の休憩がまだ終了していなければ「休憩中」
            $lastBreak = BreakTime::where('attendance_id', $attendance->id)
                ->latest()
                ->first();

            if ($lastBreak && $lastBreak->break_start && !$lastBreak->break_end) {
                $status = '休憩中';
            } else {
                $status = '出勤中';
            }
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
            'date' => Carbon::today(),
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
        $attendance = Attendance::where('user_id', Auth::id())->whereDate('created_at', today())->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤記録がありません。');
        }

        // 休憩を開始（新しいレコードを作成）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
            'break_end' => null,
        ]);

        return redirect()->back()->with('success', '休憩開始しました。');
    }

    public function endBreak()
    {
        $attendance = Attendance::where('user_id', Auth::id())->whereDate('created_at', today())->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '出勤記録がありません。');
        }

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if (!$break) {
            return redirect()->back()->with('error', '開始していない休憩がありません。');
        }

        $break->update([
            'break_end' => now(),
        ]);

        return redirect()->back()->with('success', '休憩終了しました。');
    }

    /**
     * 勤怠一覧画面を表示
     */
    public function list(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance_list', compact('attendances', 'currentMonth'));
    }

    public function show($id)
    {
        // 指定されたIDの勤怠情報を取得
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        // 修正申請を `attendance_id` で取得
        $requests = StampCorrection::with('attendance')
            ->where('attendance_id', $attendance->id)
            ->where('status', '承認待ち')
            ->latest()
            ->first();

        // 修正申請がある場合、`attendance` の `clock_in` と `clock_out` を上書き
        if ($requests) {
            $attendance->clock_in = $requests->clock_in ?? $attendance->clock_in;
            $attendance->clock_out = $requests->clock_out ?? $attendance->clock_out;
            $attendance->note = $requests->reason ?? $attendance->note;

            // 休憩情報も修正申請がある場合は BreakTime に直接反映
            if ($requests->breaks) {
                BreakTime::where('attendance_id', $attendance->id)->delete();
                foreach ($requests->breaks as $break) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $break['break_start'],
                        'break_end' => $break['break_end'],
                    ]);
                }
            }
        }

        return view('attendance_detail', compact('attendance', 'requests'));
    }

    /**
     * 勤怠修正申請の送信処理
     */
    public function submitCorrectionRequest(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // バリデーション
        $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'breaks' => 'array',
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end' => 'nullable|date_format:H:i|after:breaks.*.start',
            'note' => 'required|string|max:255',
        ]);

        // 既存の休憩時間を削除
        BreakTime::where('attendance_id', $attendance->id)->delete();

        // 休憩時間を保存
        if ($request->has('breaks')) {
            foreach ($request->breaks as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => Carbon::createFromFormat('H:i', $break['start'])->format('H:i:s'),
                        'break_end' => Carbon::createFromFormat('H:i', $break['end'])->format('H:i:s'),
                    ]);
                }
            }
        }

        // 勤怠情報の修正申請を作成
        StampCorrection::create([
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'target_date' => $attendance->date,
            'clock_in' => $request->clock_in ? Carbon::createFromFormat('H:i', $request->clock_in) : null,
            'clock_out' => $request->clock_out ? Carbon::createFromFormat('H:i', $request->clock_out) : null,
            'reason' => $request->note,
            'status' => '承認待ち',
            'applied_at' => now(),
        ]);

        return redirect()->route('attendance.detail', ['id' => $attendance->id])->with('success', '修正申請を送信しました。');
    }
}