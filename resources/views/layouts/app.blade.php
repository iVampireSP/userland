<!doctype html>
<html lang="zh_CN" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Web App 清单 -->
    <link rel="manifest" href="/manifest.webmanifest">

    <title>@yield('title', config('app.display_name'))</title>

    <link rel="icon" href="{{ asset('/images/fav-1.ico') }}" />
    <link rel="apple-touch-icon" href="{{ asset('/images/fav-1.ico') }}" />

    <!-- Fonts -->
    {{--
    <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
    {{--
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> --}}

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://www.recaptcha.net/recaptcha/api.js" async defer></script>
    <script>
        window.RECAPTCHA_SITE_KEY = "{{ config('recaptcha.site_key') }}";
    </script>
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg bd-navbar sticky-top bg-body" id="nav">
            <div class="container">
                <a class="navbar-brand" href="{{ route('index') }}">
                    <div class="d-flex">
                        <img src="{{ asset('/images/fav-1.ico') }}" alt="Logo" width="24" />
                        @yield('subtitle', config('app.display_name'))
                    </div>
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
                                <a class="nav-link" href="{{ route('push-subscription.show') }}">通知</a>
                            </li>


                            <li class="nav-item dropdown">
                                <a id="navbarDropdownAuth" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    认证
                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdownAuth">
                                    {{-- <a class="dropdown-item" href="{{ route('faces.index') }}">人脸识别</a> --}}
                                    <a class="dropdown-item" href="{{ route('tokens.index') }}">访问密钥</a>
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdownClient" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    OAuth 客户端
                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdownClient">
                                    <a class="dropdown-item" href="{{ route('clients.index') }}">客户端</a>
                                    <a class="dropdown-item" href="{{ route('tokens.scopes') }}">作用域</a>
                                </div>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdownPermissions" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    权限
                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdownPermissions">
                                    <a class="dropdown-item" href="{{ route('roles.index') }}">角色</a>
                                    <a class="dropdown-item" href="{{ route('permissions.index') }}">权限</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdownBilling" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    计费
                                </a>

                                <div class="dropdown-menu" aria-labelledby="navbarDropdownBilling">
                                    <a class="dropdown-item" href="{{ route('balances.index') }}">余额</a>
                                    <a class="dropdown-item" href="{{ route('packages.index') }}">会员</a>
                                    <a class="dropdown-item" href="{{ route('orders.index') }}">订单</a>
                                    <a class="dropdown-item" href="{{ route('units.price') }}">计价单位</a>
                                </div>
                            </li>

                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @if (Auth::guard('admin')->check())
                            <li class="nav-item">
                                @if (Auth::guard('web')->check())
                                    <a class="nav-link" href="{{ route('admin.users.edit', Auth::guard('web')->id()) }}">回到
                                        {{ Auth::guard('admin')->user()->name }}</a>
                                @else
                                    <a class="nav-link" href="{{ route('admin.index') }}">切换到后台</a>
                                @endif
                            </li>
                        @endif

                        @php
                            $multiUser = new \App\Support\Auth\MultiUserSupport();
                            $multiUserCount = $multiUser->count();
                        @endphp
                        <li class="nav-item dropdown @guest('web') @if (!$multiUserCount) d-none @endif  @endguest">
                            <a id="navbarDropdown-switchUser" class="nav-link dropdown-toggle" href="#" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                切换账户 @if ($multiUserCount)
                                    ({{$multiUserCount}})
                                @endif
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown-switchUser">
                                <x-switch-account :type="\App\View\Components\SwitchAccount::TYPE_DROPDOWN" />

                                <a class="dropdown-item" href="{{ route('login') }}">添加账号</a>

                                @auth('web')
                                    <a class="dropdown-item" href="#"
                                        onclick="document.getElementById('logout-all-form').submit();return false;">
                                        登出全部
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
                                                                    ?->isRealNamed()
                                                        )
                                                                                        <a class="dropdown-item" href="{{ route('real_name.create') }}">实名认证</a>
                                                        @endif
                                                        <a class="dropdown-item" href="{{ route('bans.index') }}">封禁列表</a>

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
                                                        <form id="logout-all-form" action="{{ route('logout.all') }}" method="POST"
                                                            class="d-none">
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

        const app = document.getElementById('app');
        app.style.display = 'none';
        document.addEventListener('DOMContentLoaded', function () {
            app.style.display = '';
        });
    </script>

    <x-switch-account :type="\App\View\Components\SwitchAccount::TYPE_ELEMENT" />


</body>

</html>
