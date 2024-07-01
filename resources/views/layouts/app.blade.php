<!doctype html>
<html lang="zh_CN" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.display_name'))</title>


{{--    <link rel="icon" href="{{ asset('/images/lae-fav.png') }}" />--}}
{{--    <link rel="apple-touch-icon" href="{{ asset('/images/lae-fav.png') }}" />--}}

    <!-- Fonts -->
    {{-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
    {{-- <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> --}}

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg bd-navbar sticky-top bg-body" id="nav">
            <div class="container">
                <a class="navbar-brand" href="{{ route('index') }}">
                    {{ config('app.display_name') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="bi bi-list fs-1"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth('web')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('index') }}">{{ auth('web')->user()->name }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('faces.index') }}">人脸识别</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('tokens.index') }}">访问密钥</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('clients.index') }}">OAuth2 客户端</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('tokens.scopes') }}">令牌范围</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('bans.index') }}">封禁列表</a>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @if (Auth::guard('admin')->check())
                            <li class="nav-item">
                                @if (Auth::guard('web')->check())
                                    <a class="nav-link"
                                        href="{{ route('admin.users.edit', Auth::guard('web')->id()) }}">回到
                                        {{ Auth::guard('admin')->user()->name }}</a>
                                @else
                                    <a class="nav-link" href="{{ route('admin.index') }}">切换到后台</a>
                                @endif
                            </li>
                        @endif

                        @php
                            $multiUser = new App\Support\MultiUserSupport();
                            $multiUserCount = $multiUser->count();
                        @endphp
{{--                        切换账户 --}}
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown-switchUser" class="nav-link dropdown-toggle" href="#" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                切换账户 @if ($multiUserCount) ({{$multiUserCount}})@endif
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown-switchUser">
                                <x-switch-account :type="\App\View\Components\SwitchAccount::TYPE_DROPDOWN" />

                                <a class="dropdown-item" href="{{ route('login') }}">添加账号</a>

                                @auth('web')
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="document.getElementById('logout-form').submit();return false;">
                                        {{ __('Logout') }}
                                    </a>
                                @endauth
                            </div>
                        </li>

                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </a>


                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('password.request') }}">
                                        {{ __('Reset Password') }}
                                    </a>

                                    @if (
                                        !auth('web')->user()
                                            ?->isRealNamed())
                                        <a class="dropdown-item" href="{{ route('real_name.create') }}">实名认证</a>
                                    @endif

                                    <a class="dropdown-item" href="{{ route('tos') }}">服务条款</a>
                                    <a class="dropdown-item" href="{{ route('privacy_policy') }}">隐私政策</a>

                                    <a class="dropdown-item" href="{{ route('users.delete') }}">删除账号</a>


                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="document.getElementById('logout-form').submit();return false;">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <x-alert />
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        @yield('content')
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        @if (session('auth.password_confirmed_at'))
            const nav = document.getElementById('nav');
            nav.style.backgroundColor = 'rgb(234 234 234 / 9%)';
            nav.classList.remove('bg-body');
        @endif
    </script>

    <x-switch-account :type="\App\View\Components\SwitchAccount::TYPE_ELEMENT" />


</body>

</html>
