@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('title', '勤怠登録')

@section('content')
<div class="attendance-container">
    <p class="status-label">{{ $status }}</p>
    <p class="date">{{ $date }}</p>
    <p class="time">{{ $time }}</p>

    @if($status === '勤務外')
    <form method="POST" action="{{ route('attendance.start') }}">
        @csrf
        <button type="submit" class="btn btn-black">出勤</button>
    </form>
    @elseif($status === '出勤中')
    <form method="POST" action="{{ route('attendance.end') }}">
        @csrf
        <button type="submit" class="btn btn-black">退勤</button>
    </form>
    <form method="POST" action="{{ route('attendance.break.start') }}">
        @csrf
        <button type="submit" class="btn btn-white">休憩入</button>
    </form>
    @elseif($status === '休憩中')
    <form method="POST" action="{{ route('attendance.break.end') }}">
        @csrf
        <button type="submit" class="btn btn-white">休憩戻</button>
    </form>
    @elseif($status === '退勤済')
    <p class="message">お疲れ様でした。</p>
    @endif
</div>
@endsection