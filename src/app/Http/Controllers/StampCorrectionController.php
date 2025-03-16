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
    public function index()
    {
        $requests = StampCorrection::where('user_id', Auth::id())->latest()->get();
        return view('stamp_correction_list', compact('requests'));
    }

    /**
     * 申請の作成
     */
    public function store(Request $request)
    {
        $request->validate([
            'target_date' => 'required|date',
            'reason' => 'required|max:255',
        ]);

        StampCorrection::create([
            'user_id' => Auth::id(),
            'target_date' => $request->target_date,
            'reason' => $request->reason,
            'status' => '承認待ち',
        ]);

        return redirect()->route('stamp_correction.list')->with('success', '申請を送信しました。');
    }
}
