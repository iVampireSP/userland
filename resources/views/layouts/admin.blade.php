@php use Illuminate\Support\Facades\Auth; @endphp
    <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '管理员')</title>

    <!-- Fonts -->
    {{-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
    {{-- <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> --}}

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
<div id="app">
    <nav class="navbar navbar-expand-lg bd-navbar sticky-top" id="nav" style="background: rgb(234 234 234 / 9%)">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.index') }}">
                {{ config('app.display_name') }} - 管理员
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="bi bi-list fs-1"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                @auth('admin')
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">用户</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.clients.index') }}">OAuth 客户端</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                连接
                            </a>

                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.applications.index') }}">应用</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.push_apps.index') }}">推送</a></li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.notifications.create') }}">通知</a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                商店
                            </a>

                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item"
                                       href="{{ route('admin.package_categories.index') }}">分类</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.packages.index') }}">套餐</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.unit_prices.index') }}">计价单位</a></li>
                                {{--                                <li><a class="dropdown-item" href="{{ route('admin.quotas.index') }}">配额</a></li>--}}
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">
                                权限
                            </a>

                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.roles.index') }}">角色</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.permissions.index') }}">权限</a></li>
                            </ul>
                        </li>


                        {{--                        <li class="nav-item dropdown">--}}
                        {{--                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"--}}
                        {{--                               aria-expanded="false">--}}
                        {{--                                订阅--}}
                        {{--                            </a>--}}

                        {{--                            <ul class="dropdown-menu">--}}
                        {{--                                <li><a class="dropdown-item" href="{{ route('admin.plans.index') }}">计划</a></li>--}}
                        {{--                                <li><a class="dropdown-item" href="{{ route('admin.features.index') }}">功能</a></li>--}}
                        {{--                            </ul>--}}
                        {{--                        </li>--}}

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.admins.index') }}">管理员</a>
                        </li>
                    </ul>
                @endauth

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    <!-- Authentication Links -->
                    @if (!Auth::guard('admin')->check())
                        @if (Route::has('admin.login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif
                    @else
                        @if (Auth::guard('web')->check())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('index') }}">切换到
                                    {{ Auth::guard('web')->user()->name }}</a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">

                            @php($admin = Auth::guard('admin')->user())

                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ $admin->name ?? '管理员' }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('admin.admins.edit', $admin) }}">
                                    编辑资料
                                </a>

                                <a class="dropdown-item" href="{{ route('admin.logout') }}"
                                   onclick="document.getElementById('logout-form').submit();return false;">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST"
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
        <x-alert/>

        <div class="container">
            @yield('content')
        </div>
    </main>

    <div class="mt-5"></div>

</div>

<script>
    const app = document.getElementById('app');
    app.style.display = 'none';
    document.addEventListener('DOMContentLoaded', function () {
        app.style.display = '';
    });
</script>
</body>

</html>
