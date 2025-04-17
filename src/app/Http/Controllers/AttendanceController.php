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

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'desc')
            ->get();

        return view('attendance_list', compact('attendances', 'currentMonth'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        $requests = StampCorrection::with('attendance')
            ->where('attendance_id', $attendance->id)
            ->where('status', '承認待ち')
            ->latest()
            ->first();

        if ($requests) {
            $attendance->clock_in = $requests->clock_in ?? $attendance->clock_in;
            $attendance->clock_out = $requests->clock_out ?? $attendance->clock_out;
            $attendance->note = $requests->reason ?? $attendance->note;

            $breaks = is_string($requests->breaks) ? json_decode($requests->breaks, true) : $requests->breaks;

            if (is_array($breaks)) {
                BreakTime::where('attendance_id', $attendance->id)->delete();

                foreach ($breaks as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($attendance->date)->toDateString() . ' ' . $break['start'])->format('Y-m-d H:i:s'),
                            'break_end' => Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($attendance->date)->toDateString() . ' ' . $break['end'])->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        // ログインユーザーが管理者かどうかを判定
        $isAdmin = Auth::guard('admin')->check();

        return view('attendance_detail', [
            'attendance' => $attendance,
            'requests' => $requests,
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

        if ($user instanceof \App\Models\Admin) {
            // 管理者は即時反映
            $attendance->date = $request->input('date');
            $attendance->clock_in = Carbon::createFromFormat('Y-m-d H:i', $attendance->date . ' ' . $request->input('clock_in'))->format('Y-m-d H:i:s');
            $attendance->clock_out = Carbon::createFromFormat('Y-m-d H:i', $attendance->date . ' ' . $request->input('clock_out'))->format('Y-m-d H:i:s');
            $attendance->save();

            // 休憩の更新
            foreach ($request->input('breaks', []) as $breakData) {
                $date = $request->input('date', $attendance->date);

                $start = $breakData['start'] ?? null;
                $end = $breakData['end'] ?? null;

                if ($start !== null && $end !== null && $start !== '' && $end !== '') {
                    if (!empty($breakData['id'])) {
                        $break = BreakTime::find($breakData['id']);
                        if ($break) {
                            $break->update([
                                'break_start' => Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($attendance->date)->toDateString() . ' ' . $start)->format('Y-m-d H:i:s'),
                                'break_end' => Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($attendance->date)->toDateString() . ' ' . $end)->format('Y-m-d H:i:s'),
                            ]);
                        }
                    } else {
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($attendance->date)->toDateString() . ' ' . $start)->format('Y-m-d H:i:s'),
                            'break_end' => Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($attendance->date)->toDateString() . ' ' . $end)->format('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            return redirect()->route('attendance.detail', ['id' => $id])->with('success', '勤怠を更新しました。');
        }

        // 一般ユーザーは修正申請
        StampCorrection::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'target_date' => $attendance->date,
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'reason' => $request->input('note'),
            'status' => '承認待ち',
            'applied_at' => now(),
            'breaks' => json_encode($request->input('breaks', [])),
        ]);

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

        // 更新対象の休憩情報をリセット
        BreakTime::where('attendance_id', $attendance->id)->delete();

        // 再登録
        foreach ($request->input('breaks', []) as $break) {
            $start = $break['start'] ?? null;
            $end = $break['end'] ?? null;

            if (!empty($start) && !empty($end)) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => Carbon::createFromFormat('Y-m-d H:i', $newDate . ' ' . $start)->format('Y-m-d H:i:s'),
                    'break_end' => Carbon::createFromFormat('Y-m-d H:i', $newDate . ' ' . $end)->format('Y-m-d H:i:s'),
                ]);
            }
        }

        // 月次一覧での反映のため、リダイレクト先を修正
        $month = Carbon::parse($newDate)->format('Y-m');

        return redirect()->route('attendance.list', ['month' => $month])->with('success', '勤怠情報を更新しました。');
    }
}