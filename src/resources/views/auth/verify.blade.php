@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <p>登録していただいたメールアドレスに認証メールを送付しました。メール認証を完了してください。</p>
    <a href="{{ route('verification.notice') }}" class="button">認証はこちらから</a>
    <a href="{{ route('verification.resend') }}">認証メールを再送する</a>
</div>
@endsection