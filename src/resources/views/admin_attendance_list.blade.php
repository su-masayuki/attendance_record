@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('title', '勤怠一覧')

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
            <tr>
                <td>{{ optional($attendance->user)->name ?? '不明' }}</td>
                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->break_time ?? '-' }}</td>
                <td>{{ $attendance->total_time ?? '-' }}</td>
                <td><a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection