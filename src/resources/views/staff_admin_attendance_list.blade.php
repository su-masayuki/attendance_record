@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_admin_attendance_list.css') }}">
@endsection

@section('title', $staff->name . 'さんの勤怠')

@section('content')
<div class="attendance-list-container">
    <h1>{{ $staff->name }}さんの勤怠</h1>
    <div class="date-navigation">
        <button class="prev-month">&larr; 前月</button>
        <span class="current-month">{{ $month }}</span>
        <button class="next-month">翌月 &rarr;</button>
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
                <td>{{ $attendance->total_hours }}</td>
                <td><a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <button class="csv-export">CSV出力</button>
</div>
@endsection