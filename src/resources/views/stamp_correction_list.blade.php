@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_list.css') }}">
@endsection

@section('title', '申請一覧')

@section('content')
<div class="request-list-container">
    <h1>申請一覧</h1>
    <div class="status-tabs">
        <a href="{{ route('stamp_correction_request.list', ['status' => '承認待ち']) }}" class="{{ request('status', '承認待ち') === '承認待ち' ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('stamp_correction_request.list', ['status' => '承認済み']) }}" class="{{ request('status') === '承認済み' ? 'active' : '' }}">承認済み</a>
    </div>

    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
            <tr>
                <td>{{ $request->status }}</td>
                <td>{{ $request->user->name }}</td>
                <td>{{ \Carbon\Carbon::parse($request->target_date)->format('Y/m/d') }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td>
                    @if ($request->attendance_id)
                        <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}">詳細</a>
                    @else
                        ー
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection