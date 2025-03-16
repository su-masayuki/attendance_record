<!-- resources/views/admin/staff_list.blade.php -->
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

@section('title', 'スタッフ一覧')

@section('content')
<div class="staff-list-container">
    <h1>スタッフ一覧</h1>
    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staffs as $staff)
            <tr>
                <td>{{ $staff->name }}</td>
                <td>{{ $staff->email }}</td>
                <td><a href="{{ route('admin.attendance.staff', $staff->id) }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection