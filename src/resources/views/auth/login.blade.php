@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <h2>ログイン</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="email" name="email" placeholder="メールアドレス" value="{{ old('email') }}" required>
        @error('email')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <input type="password" name="password" placeholder="パスワード" required>
        @error('password')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <button type="submit">ログインする</button>
    </form>
    <a href="{{ route('register') }}">会員登録はこちら</a>
</div>
@endsection