<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="/">COACHTECH</a>
            </div>
            @auth
                @if(Auth::user()->is_admin)
                {{-- 管理者用ヘッダー --}}
                <nav>
                    <ul>
                        <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                        <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                        <li><a href="{{ route('admin.request.list') }}">申請一覧</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
                @else
                {{-- 一般ユーザー用ヘッダー --}}
                <nav>
                    <ul>
                        <li><a href="{{ route('attendance') }}">勤怠</a></li>
                        <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                        <li><a href="{{ route('stamp_correction_request.list') }}">申請</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
                @endif
            @endauth
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>