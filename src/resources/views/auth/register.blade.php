@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h2>会員登録</h2>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <input type="text" name="name" placeholder="名前" value="{{ old('name') }}" required>
        @error('name')
            <div class="error-message">{{ $message }}</div>
        @enderror
        <input type="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}" required>
        @error('email')
            <div class="error-message">{{ $message }}</div>
        @enderror
        <input type="password" name="password" placeholder="パスワード" required autocomplete="new-password">
        @error('password')
            <div class="error-message">{{ $message }}</div>
        @enderror
        <input type="password" name="password_confirmation" placeholder="パスワード確認" required autocomplete="new-password">
        @error('password_confirmation')
            <div class="error-message">{{ $message }}</div>
        @enderror
        <button type="submit">登録する</button>
    </form>
    <a href="{{ route('login') }}">ログインはこちら</a>
</div>
@endsection