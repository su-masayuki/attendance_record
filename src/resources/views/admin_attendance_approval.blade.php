@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_approval.css') }}">
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
                {{ $attendance->clock_in }} ~ {{ $attendance->clock_out }}
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                {{ $attendance->break_start }} ~ {{ $attendance->break_end }}
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                {{ $attendance->break_start_2 ?? '—' }} ~ {{ $attendance->break_end_2 ?? '—' }}
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>
                {{ $attendance->note ?? '—' }}
            </td>
        </tr>
    </table>

    @if($attendance->status === '承認済み')
    <button class="approved-button" disabled>承認済み</button>
    @else
    <form action="{{ route('admin.attendance.approve', $attendance->id) }}" method="post">
        @csrf
        <button type="submit" class="approve-button">承認</button>
    </form>
    @endif
</div>
@endsection