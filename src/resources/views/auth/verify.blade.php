@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <p>登録していただいたメールアドレスに認証メールを送付しました。メール認証を完了してください。</p>
    <a href="{{ route('verification.notice') }}" class="button">認証はこちらから</a>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="button">認証メールを再送する</button>
    </form>
</div>
@endsection