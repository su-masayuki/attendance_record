@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('title', '管理者ログイン')

@section('content')
<div class="auth-container">
    <h1>管理者ログイン</h1>
    <form action="{{ route('admin.login') }}" method="post">
        @csrf
        <div class="input-container">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div class="input-container">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password">
            @error('password')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div class="btn-container">
            <button type="submit" class="login-button">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection