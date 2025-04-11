@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_approval.css') }}">
@endsection

@section('title', '修正申請承認')

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>
    <table class="attendance-table">
    @php
        $latestCorrection = $attendance->stampCorrections()->latest()->first();
        $clockIn = $latestCorrection->clock_in ?? $attendance->clock_in;
        $clockOut = $latestCorrection->clock_out ?? $attendance->clock_out;
        $note = $latestCorrection->reason ?? $attendance->note;

        $totalBreakMinutes = 0;
        foreach ($attendance->breakTimes ?? [] as $break) {
            $start = \Carbon\Carbon::parse($break->break_start);
            $end = \Carbon\Carbon::parse($break->break_end);
            $totalBreakMinutes += $end->diffInMinutes($start);
        }
        $totalBreakFormatted = \Carbon\CarbonInterval::minutes($totalBreakMinutes)->cascade()->format('%H:%I');
    @endphp
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ $attendance->date->format('Y年m月d日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ \Carbon\Carbon::parse($clockIn)->format('H:i') }} ~ {{ \Carbon\Carbon::parse($clockOut)->format('H:i') }}
            </td>
        </tr>
        @foreach ($attendance->breakTimes as $index => $break)
        <tr>
            <th>休憩{{ $index + 1 }}</th>
            <td>
                {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }} ~ {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}
            </td>
        </tr>
        @endforeach
        <tr>
            <th>備考</th>
            <td>
                {{ $note ?? '—' }}
            </td>
        </tr>
    </table>

    <form action="{{ route('admin.attendance.approve', ['attendance_correct_request' => $latestCorrection->id]) }}" method="post">
        @csrf
        <input type="hidden" name="clock_in" value="{{ $latestCorrection->clock_in }}">
        <input type="hidden" name="clock_out" value="{{ $latestCorrection->clock_out }}">
        <input type="hidden" name="note" value="{{ $latestCorrection->reason }}">
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <button type="submit" class="{{ $attendance->status === '承認済み' ? 'approved-button' : 'approve-button' }}" {{ $attendance->status === '承認済み' ? 'disabled' : '' }}>
            {{ $attendance->status === '承認済み' ? '承認済み' : '承認' }}
        </button>
    </form>
</div>
@endsection