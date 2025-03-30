@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('title', '勤怠詳細')

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>
    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <table class="attendance-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ $attendanceDate ? $attendanceDate->format('Y年m月d日') : '日付未設定' }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ $attendance->clock_in }}">
                    ~
                    <input type="time" name="clock_out" value="{{ $attendance->clock_out }}">
                </td>
            </tr>
            @foreach($attendance->breakTimes as $key => $break)
            <tr>
                <th>休憩{{ $key + 1 }}</th>
                <td>
                    <input type="time" name="break_start[{{ $key }}]" value="{{ $break->break_start }}">
                    ~
                    <input type="time" name="break_end[{{ $key }}]" value="{{ $break->break_end }}">
                </td>
            </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note">{{ $attendance->note }}</textarea>
                </td>
            </tr>
        </table>
        <button type="submit" class="update-button">修正</button>
    </form>
</div>
@endsection