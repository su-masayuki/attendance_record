@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff_attendance_list.css') }}">
@endsection

@section('title', $staff->name . 'さんの勤怠')

@section('content')
<div class="attendance-list-container">
    <h1>{{ $staff->name }}さんの勤怠</h1>
    <div class="date-navigation">
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="prev-month">&larr; 前月</a>
        <span class="current-month">{{ $month->format('Y年m月') }}</span>
        <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="next-month">翌月 &rarr;</a>
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
                <td>{{ optional(\Carbon\Carbon::parse($attendance->clock_in))->format('H:i') ?? '-' }}</td>
                <td>{{ optional(\Carbon\Carbon::parse($attendance->clock_out))->format('H:i') ?? '-' }}</td>
                <td>
                    @php
                        $totalBreakMinutes = 0;
                        if (!empty($attendance->breakTimes) && is_iterable($attendance->breakTimes)) {
                            $totalBreakMinutes = $attendance->breakTimes->reduce(function ($carry, $break) {
                                $start = \Carbon\Carbon::parse($break->break_start);
                                $end = \Carbon\Carbon::parse($break->break_end);
                                return $carry + ($end && $start ? $end->diffInMinutes($start) : 0);
                            }, 0);
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
                <td>
                    <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('admin.attendance.csv', ['id' => $staff->id]) }}" class="csv-export">CSV出力</a>
</div>
@endsection