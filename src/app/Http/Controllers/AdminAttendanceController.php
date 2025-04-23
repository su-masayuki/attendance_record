<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceUpdateRequest;
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
        $attendances = Attendance::with('breakTimes')->whereDate('created_at', $selectedDate->toDateString())->get();

        return view('admin_attendance_list', compact('attendances', 'selectedDate'));
    }

    public function show($id)
    {
        // 指定されたIDの勤怠情報を取得
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        // `date` カラムが null の場合は `created_at` を使用
        $attendanceDate = $attendance->date ? Carbon::parse($attendance->date)->format('Y/m/d') : Carbon::parse($attendance->created_at)->format('Y/m/d');

        $latestRequest = StampCorrection::with('correctionBreaks')
            ->where('attendance_id', $attendance->id)
            ->latest()
            ->first();

        return view('admin_attendance_detail', compact('attendance', 'attendanceDate', 'latestRequest'));
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
        $attendance->save();
 
        // 既存の break_times を削除
        $attendance->breakTimes()->delete();
 
        // 修正申請に登録されている休憩時間を break_times に反映
        $correction->load('correctionBreaks');

        foreach ($correction->correctionBreaks as $break) {
            if ($break->break_start && $break->break_end) {
                $attendance->breakTimes()->create([
                    'break_start' => $break->break_start,
                    'break_end' => $break->break_end,
                ]);
            }
        }
 
        // ステータスを承認済みに
        $correction->status = '承認済み';
        $correction->save();
 
        // 承認後に確認画面へリダイレクト（必要なら一覧でもOK）
        return redirect()->route('admin.attendance.approval', ['attendance_correct_request' => $correction->id])
                         ->with('success', '修正申請を承認しました。');
    }
    public function update(AdminAttendanceUpdateRequest $request, $id)
    {

        $attendance = Attendance::findOrFail($id);
        $attendance->clock_in = Carbon::parse($attendance->date)->setTimeFromTimeString($request->clock_in);
        $attendance->clock_out = Carbon::parse($attendance->date)->setTimeFromTimeString($request->clock_out);
        $attendance->stampCorrection()->updateOrCreate(
            ['attendance_id' => $attendance->id],
            [
                'reason' => $request->note,
                'user_id' => $attendance->user_id,
                'target_date' => $attendance->date ?? $attendance->created_at->toDateString(),
                'status' => '承認済み',
            ]
        );
        $attendance->save();

        // 休憩時間の再登録（省略可）
        $attendance->breakTimes()->delete();
        if ($request->has('breaks')) {
            foreach ($request->input('breaks') as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => Carbon::parse($attendance->date)->setTimeFromTimeString($break['start']),
                        'break_end' => Carbon::parse($attendance->date)->setTimeFromTimeString($break['end']),
                    ]);
                }
            }
        }

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id])
                         ->with('success', '勤怠情報を更新しました。');
    }
}
