@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('title', '勤怠一覧')

@section('content')
<div class="attendance-list-container">
    <h1>勤怠一覧</h1>
    <div class="month-selector">
        <a href="#" class="prev-month">&larr; 前月</a>
        <span class="current-month">{{ now()->format('Y/m') }}</span>
        <a href="#" class="next-month">翌月 &rarr;</a>
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
                <td>{{ $attendance->date }}</td>
                <td>{{ $attendance->clock_in }}</td>
                <td>{{ $attendance->clock_out }}</td>
                <td>{{ $attendance->break_time }}</td>
                <td>{{ $attendance->total_time }}</td>
                <td><a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection