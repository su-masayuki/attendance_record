<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrection;

class StampCorrectionController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            $query = StampCorrection::query();
        } else {
            $query = StampCorrection::where('user_id', Auth::id());
        }

        $status = $request->query('status', '承認待ち');
        $query->where('status', $status);

        $requests = $query->latest()->get();

        return view('stamp_correction_list', compact('requests', 'status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_date' => 'required|date',
            'reason' => 'required|max:255',
            'breaks.*.start' => 'nullable|date_format:H:i',
            'breaks.*.end' => 'nullable|date_format:H:i',
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

    public function approve($id)
    {
        $correction = StampCorrection::findOrFail($id);
        $attendance = $correction->attendance;

        if ($attendance) {
            $attendance->update([
                'clock_in' => $correction->clock_in ?? $attendance->clock_in,
                'clock_out' => $correction->clock_out ?? $attendance->clock_out,
                'note' => $correction->reason ?? $attendance->note,
            ]);

            if ($correction->correctionBreaks()->exists()) {
                $attendance->breakTimes()->delete();

                foreach ($correction->correctionBreaks as $break) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break->break_start,
                        'break_end' => $break->break_end,
                    ]);
                }
            }

            $correction->update(['status' => '承認済み']);
        }

        return redirect()->route('stamp_correction_request.list')->with('success', '修正申請を承認しました。');
    }
}
