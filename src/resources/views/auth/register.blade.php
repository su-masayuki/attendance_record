@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h2>会員登録</h2>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        
        <label for="name">名前</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required>
        @error('name')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="email">メールアドレス</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        @error('email')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="password">パスワード</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">
        @error('password')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="password_confirmation">パスワード確認</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        @error('password_confirmation')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <button type="submit">登録する</button>
    </form>
    <a href="{{ route('login') }}">ログインはこちら</a>
</div>
@endsection