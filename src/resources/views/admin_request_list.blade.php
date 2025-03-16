@extends('layouts.app')

@section('title', '申請一覧')

@section('content')
<div class="request-list-container">
    <h1>申請一覧</h1>
    <div class="tab-menu">
        <button class="active">承認待ち</button>
        <button>承認済み</button>
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
                <td>{{ $request->target_date->format('Y/m/d') }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td><a href="{{ route('admin.request.detail', $request->id) }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection