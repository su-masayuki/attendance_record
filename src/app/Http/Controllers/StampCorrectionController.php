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
        if (Auth::guard('admin')->check()) {
            $query = StampCorrection::query(); // 管理者は全件取得
        } else {
            $query = StampCorrection::where('user_id', Auth::id()); // 一般ユーザーは自分の申請のみ
        }

        $status = $request->query('status', '承認待ち');
        $query->where('status', $status);

        $requests = $query->latest()->get();

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
            'breaks.*.start' => 'nullable|date_format:H:i', // 変更
            'breaks.*.end' => 'nullable|date_format:H:i',   // 変更
        ]);

        $correction = StampCorrection::create([
            'user_id' => Auth::id(),
            'target_date' => $request->target_date,
            'reason' => $request->reason,
            'status' => '承認待ち',
        ]);

        foreach ($request->input('breaks') as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $correction->correctionBreaks()->create([
                    'break_start' => $break['start'],
                    'break_end' => $break['end'],
                ]);
            }
        }

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
            if ($correction->correctionBreaks()->exists()) {
                // 既存の休憩時間を削除
                $attendance->breakTimes()->delete();

                // 新しい休憩時間を挿入
                foreach ($correction->correctionBreaks as $break) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break->break_start,
                        'break_end' => $break->break_end,
                    ]);
                }
            }

            // 申請を「承認済み」に更新
            $correction->update(['status' => '承認済み']);
        }

        return redirect()->route('stamp_correction_request.list')->with('success', '修正申請を承認しました。');
    }
}
