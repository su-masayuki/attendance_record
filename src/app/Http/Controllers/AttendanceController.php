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
use App\Http\Requests\SubmitCorrectionRequest;
use App\Http\Requests\AdminAttendanceUpdateRequest;

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

        $attendances = Attendance::with('breakTimes')->where('user_id', Auth::id())
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance_list', compact('attendances', 'currentMonth'));
    }

    public function show($id)
    {
    $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

    $latestRequest = StampCorrection::with('correctionBreaks')
        ->where('attendance_id', $attendance->id)
        ->latest()
        ->first();

    $isAdmin = Auth::guard('admin')->check();

    return view('attendance_detail', [
        'attendance' => $attendance,
        'latestRequest' => $latestRequest,
        'isAdmin' => $isAdmin,
    ]);
    }

    /**
     * 勤怠修正申請の送信処理
     */
    public function submitCorrectionRequest(SubmitCorrectionRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);
        $user = auth()->user();

        // 一般ユーザーは修正申請
        $correction = StampCorrection::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'target_date' => $attendance->date,
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'reason' => $request->input('note'),
            'status' => '承認待ち',
            'applied_at' => now(),
        ]);

        foreach ($request->input('breaks', []) as $break) {
            $start = trim($break['start'] ?? '');
            $end = trim($break['end'] ?? '');

            if ($start !== '' && $end !== '') {
                $breakDate = Carbon::parse($attendance->date)->format('Y-m-d');

                $correction->correctionBreaks()->create([
                    'break_start' => Carbon::parse("{$breakDate} {$start}")->format('Y-m-d H:i:s'),
                    'break_end' => Carbon::parse("{$breakDate} {$end}")->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->route('attendance.detail', ['id' => $id])->with('success', '修正申請を送信しました。');
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $newDate = $request->input('date');
        
        $attendance->date = $newDate;
        $attendance->clock_in = Carbon::createFromFormat('Y-m-d H:i', $newDate . ' ' . $request->input('clock_in'))->format('Y-m-d H:i:s');
        $attendance->clock_out = Carbon::createFromFormat('Y-m-d H:i', $newDate . ' ' . $request->input('clock_out'))->format('Y-m-d H:i:s');
        $attendance->save();

        foreach ($request->input('breaks', []) as $break) {
            $start = $break['start'] ?? null;
            $end = $break['end'] ?? null;
            $breakId = $break['id'] ?? null;
 
            if (!empty($start) && !empty($end)) {
                $startFormatted = Carbon::createFromFormat('Y-m-d H:i', $newDate . ' ' . $start)->format('Y-m-d H:i:s');
                $endFormatted = Carbon::createFromFormat('Y-m-d H:i', $newDate . ' ' . $end)->format('Y-m-d H:i:s');
 
                if ($breakId) {
                    $existingBreak = BreakTime::find($breakId);
                    if ($existingBreak) {
                        $existingBreak->update([
                            'break_start' => $startFormatted,
                            'break_end' => $endFormatted,
                        ]);
                        continue;
                    }
                }
 
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $startFormatted,
                    'break_end' => $endFormatted,
                ]);
            }
        }

        // 月次一覧での反映のため、リダイレクト先を修正
        $month = Carbon::parse($newDate)->format('Y-m');

        return redirect()->route('admin.attendance.staff', [
            'id' => $attendance->user_id,
            'month' => $month
        ])->with('success', '勤怠情報を更新しました。');
    }
}