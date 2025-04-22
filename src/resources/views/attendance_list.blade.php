@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('title', '勤怠一覧_一般ユーザー')

@section('content')
<div class="attendance-list-container">
    <h2>勤怠一覧</h2>
    <div class="month-selector">
        <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}" class="prev-month">&larr; 前月</a>
        <span class="current-month">{{ $currentMonth->format('Y/m') }}</span>
        <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}" class="next-month">翌月 &rarr;</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD(dd)') }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</td>
                <td>
                    @php
                        $totalBreakMinutes = 0;
                        if ($attendance->relationLoaded('breakTimes') && $attendance->breakTimes instanceof \Illuminate\Support\Collection) {
                            foreach ($attendance->breakTimes as $break) {
                                if (!empty($break->break_start) && !empty($break->break_end)) {
                                    $start = \Carbon\Carbon::parse($break->break_start);
                                    $end = \Carbon\Carbon::parse($break->break_end);
                                    $totalBreakMinutes += $end->diffInMinutes($start);
                                }
                            }
                        }
                    @endphp
                    {{ $totalBreakMinutes > 0 ? \Carbon\CarbonInterval::minutes($totalBreakMinutes)->cascade()->format('%H:%I') : '00:00' }}
                </td>
                <td>
                    @php
                        $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                        $clockOut = \Carbon\Carbon::parse($attendance->clock_out);
                        $workDuration = max(0, $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes);
                    @endphp
                    {{ $workDuration > 0 ? \Carbon\CarbonInterval::minutes($workDuration)->cascade()->format('%H:%I') : '00:00' }}
                </td>
                <td><a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection