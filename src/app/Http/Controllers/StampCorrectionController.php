<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrection;

class StampCorrectionController extends Controller
{
    /**
     * 申請一覧を表示
     */
    public function index(Request $request)
    {
        $query = StampCorrection::where('user_id', Auth::id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->get();
        $status = $request->status ?? null;

        return view('stamp_correction_list', compact('requests', 'status'));
    }

    /**
     * 申請の作成
     */
    public function store(Request $request)
    {
        $request->validate([
            'target_date' => 'required|date',
            'reason' => 'required|max:255',
            'break_start' => 'nullable|date_format:H:i', // 追加
            'break_end' => 'nullable|date_format:H:i',   // 追加
        ]);

        StampCorrection::create([
            'user_id' => Auth::id(),
            'target_date' => $request->target_date,
            'reason' => $request->reason,
            'status' => '承認待ち',
            'break_start' => $request->break_start, // 追加
            'break_end' => $request->break_end,     // 追加
        ]);

        return redirect()->route('stamp_correction.list')->with('success', '申請を送信しました。');
    }

    /**
     * 申請の承認
     */
    public function approve($id)
    {
        $correction = StampCorrection::findOrFail($id);
        $attendance = $correction->attendance;

        if ($attendance) {
            // 出勤・退勤・備考の更新
            $attendance->update([
                'clock_in' => $correction->clock_in ?? $attendance->clock_in,
                'clock_out' => $correction->clock_out ?? $attendance->clock_out,
                'note' => $correction->reason ?? $attendance->note,
            ]);

            // 休憩時間の反映
            if (!empty($correction->break_start) && !empty($correction->break_end)) {
                // 既存の休憩時間を削除
                $attendance->breakTimes()->delete();

                // 新しい休憩時間を挿入
                $attendance->breakTimes()->create([
                    'break_start' => $correction->break_start,
                    'break_end' => $correction->break_end,
                ]);
            }

            // 申請を「承認済み」に更新
            $correction->update(['status' => '承認済み']);
        }

        return redirect()->route('stamp_correction_request.list')->with('success', '修正申請を承認しました。');
    }
}
