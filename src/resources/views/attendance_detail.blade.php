@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('title', '勤怠詳細')

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>

    @php
        $latestRequest = $attendance->stampCorrections()->latest()->first();
    @endphp

    <form method="POST" action="{{ route('attendance.request', ['id' => $attendance->id]) }}">
        @csrf
        <table class="attendance-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y年n月j日') : \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ optional(\Carbon\Carbon::parse($attendance->clock_in))->format('H:i') }}" 
                           {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="clock_out" value="{{ optional(\Carbon\Carbon::parse($attendance->clock_out))->format('H:i') }}" 
                           {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                </td>
            </tr>
            @if ($attendance->breakTimes && $attendance->breakTimes->count())
                @foreach ($attendance->breakTimes as $index => $break)
                <tr>
                    <th>休憩{{ $index + 1 }}</th>
                    <td>
                        <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                        <input type="time" name="breaks[{{ $index }}][start]" value="{{ optional(\Carbon\Carbon::parse($break->break_start))->format('H:i') }}" 
                               {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                        〜
                        <input type="time" name="breaks[{{ $index }}][end]" value="{{ optional(\Carbon\Carbon::parse($break->break_end))->format('H:i') }}" 
                               {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                    </td>
                </tr>
                @endforeach
            @endif
            @php $nextBreakIndex = optional($attendance->breakTimes)->count() ?? 0; @endphp
            <tr>
                <th>休憩{{ $nextBreakIndex + 1 }}</th>
                <td>
                    <input type="hidden" name="breaks[{{ $nextBreakIndex }}][id]" value="">
                    <input type="time" name="breaks[{{ $nextBreakIndex }}][start]"
                           value="{{ old('breaks.' . $nextBreakIndex . '.start') ?: '' }}"
                           {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="breaks[{{ $nextBreakIndex }}][end]"
                           value="{{ old('breaks.' . $nextBreakIndex . '.end') ?: '' }}"
                           {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                        {{ trim(old('note', $latestRequest && $latestRequest->reason ? $latestRequest->reason : ($attendance->note ?? ''))) }}
                    </textarea>
                </td>
            </tr>
        </table>

        @if ($latestRequest && $latestRequest->status === '承認待ち')
            <div class="alert-message">※ 承認待ちのため修正はできません。</div>
        @else
            <button type="submit" class="submit-button">修正</button>
        @endif
    </form>
</div>
@endsection
