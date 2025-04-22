@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('title', '勤怠詳細')

@section('content')
<div class="attendance-detail-container">
    <h1>勤怠詳細</h1>

    @php
        $isAdmin = Auth::guard('admin')->check();
        // Always prioritize correctionBreaks if available
        $correctedBreaks = ($latestRequest && $latestRequest->correctionBreaks) ? $latestRequest->correctionBreaks : ($attendance->breakTimes ?? []);
    @endphp

    <form method="POST" action="{{ $isAdmin ? route('admin.attendance.update', ['id' => $attendance->id]) : route('attendance.request', ['id' => $attendance->id]) }}">
        @csrf
        @if ($isAdmin)
            @method('POST')
        @endif
        <table class="attendance-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    @if ($isAdmin)
                        <input type="date" name="date" value="{{ $attendance->date->format('Y-m-d') }}" {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                    @else
                        {{ $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y年n月j日') : \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in"
                           value="{{ old('clock_in', ($isAdmin && $latestRequest && $latestRequest->status === '承認待ち' && $latestRequest->clock_in) ? \Carbon\Carbon::parse($latestRequest->clock_in)->format('H:i') : ($isAdmin && $latestRequest && $latestRequest->status === '承認待ち' ? optional(\Carbon\Carbon::parse($attendance->clock_in))->format('H:i') : (($latestRequest && $latestRequest->clock_in) ? \Carbon\Carbon::parse($latestRequest->clock_in)->format('H:i') : optional(\Carbon\Carbon::parse($attendance->clock_in))->format('H:i')))) }}"
                           {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="clock_out"
                           value="{{ old('clock_out', ($isAdmin && $latestRequest && $latestRequest->status === '承認待ち' && $latestRequest->clock_out) ? \Carbon\Carbon::parse($latestRequest->clock_out)->format('H:i') : ($isAdmin && $latestRequest && $latestRequest->status === '承認待ち' ? optional(\Carbon\Carbon::parse($attendance->clock_out))->format('H:i') : (($latestRequest && $latestRequest->clock_out) ? \Carbon\Carbon::parse($latestRequest->clock_out)->format('H:i') : optional(\Carbon\Carbon::parse($attendance->clock_out))->format('H:i')))) }}"
                           {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                    @error('clock_in')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @if ($correctedBreaks && count($correctedBreaks))
                @foreach ($correctedBreaks as $index => $break)
                <tr>
                    <th>休憩{{ $index + 1 }}</th>
                    <td>
                        <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id ?? '' }}">
                        <input type="time" name="breaks[{{ $index }}][start]" value="{{ optional(\Carbon\Carbon::parse($break->break_start))->format('H:i') }}"
                               {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                        〜
                        <input type="time" name="breaks[{{ $index }}][end]" value="{{ optional(\Carbon\Carbon::parse($break->break_end))->format('H:i') }}"
                               {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                        @error("breaks.{$index}.start")
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error("breaks.{$index}.end")
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @endforeach
            @endif
            @php
                $nextBreakIndex = isset($correctedBreaks) ? count($correctedBreaks) : 0;
            @endphp
            <tr>
                <th>休憩{{ $nextBreakIndex + 1 }}</th>
                <td>
                    <input type="hidden" name="breaks[{{ $nextBreakIndex }}][id]" value="">
                    <input type="time" name="breaks[{{ $nextBreakIndex }}][start]"
                           value="{{ old('breaks.' . $nextBreakIndex . '.start', '') }}"
                           {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="breaks[{{ $nextBreakIndex }}][end]"
                           value="{{ old('breaks.' . $nextBreakIndex . '.end', '') }}"
                           {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>
                    @error("breaks.{$nextBreakIndex}.start")
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error("breaks.{$nextBreakIndex}.end")
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" {{ $latestRequest && $latestRequest->status === '承認待ち' ? 'disabled' : '' }}>{{ trim(old('note', $latestRequest && $latestRequest->reason ? $latestRequest->reason : ($attendance->stampCorrection->reason ?? ''))) }}</textarea>
                    @error('note')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        @if ($latestRequest && $latestRequest->status === '承認待ち')
            <div class="alert-message">※ 承認待ちのため修正はできません。</div>
        @else
            <button type="submit" class="submit-button">{{'修正'}}</button>
        @endif
    </form>
</div>
@endsection
