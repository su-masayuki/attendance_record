@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('title', '勤怠一覧_管理者')

@section('content')
<div class="attendance-list-container">
    <h1>{{ $selectedDate instanceof \Carbon\Carbon ? $selectedDate->format('Y年m月d日') : $selectedDate }}の勤怠</h1>
    <div class="date-navigation">
        <a href="{{ route('admin.attendance.list', ['date' => $selectedDate instanceof \Carbon\Carbon ? $selectedDate->copy()->subDay()->format('Y-m-d') : \Carbon\Carbon::parse($selectedDate)->subDay()->format('Y-m-d')]) }}">&larr; 前日</a>
        <span>{{ $selectedDate instanceof \Carbon\Carbon ? $selectedDate->format('Y/m/d') : $selectedDate }}</span>
        <a href="{{ route('admin.attendance.list', ['date' => $selectedDate instanceof \Carbon\Carbon ? $selectedDate->copy()->addDay()->format('Y-m-d') : \Carbon\Carbon::parse($selectedDate)->addDay()->format('Y-m-d')]) }}">翌日 &rarr;</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            @php
                $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in) : null;
                $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out) : null;

                // 休憩時間の合計を計算
                $totalBreakMinutes = $attendance->breakTimes->sum(function($break) {
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = \Carbon\Carbon::parse($break->break_end);
                    return $start && $end ? $start->diffInMinutes($end) : 0;
                });

                // 勤務時間の計算
                $totalMinutes = $clockIn && $clockOut ? $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes : 0;

                // フォーマット変換
                $breakTimeFormatted = floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);
                $totalTimeFormatted = floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT);
            @endphp
            <tr>
                <td>{{ optional($attendance->user)->name ?? '不明' }}</td>
                <td>{{ $clockIn ? $clockIn->format('H:i') : '-' }}</td>
                <td>{{ $clockOut ? $clockOut->format('H:i') : '-' }}</td>
                <td>{{ $totalBreakMinutes > 0 ? $breakTimeFormatted : '-' }}</td>
                <td>{{ $totalMinutes > 0 ? $totalTimeFormatted : '-' }}</td>
                <td><a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection