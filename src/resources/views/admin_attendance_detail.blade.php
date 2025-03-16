@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('title', '勤怠詳細')

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>
    <table class="attendance-table">
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
                <input type="time" value="{{ $attendance->clock_in }}"> ~
                <input type="time" value="{{ $attendance->clock_out }}">
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                <input type="time" value="{{ $attendance->break_start }}"> ~
                <input type="time" value="{{ $attendance->break_end }}">
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                <input type="time" value="">
                ~
                <input type="time" value="">
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>
                <textarea>{{ $attendance->note }}</textarea>
            </td>
        </tr>
    </table>
    <button class="update-button">修正</button>
</div>
@endsection