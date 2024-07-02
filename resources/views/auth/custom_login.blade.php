@extends('layouts.app')

@section('title', $client->name)
@section('subtitle', $client->name)

@section('content')
<section>
    <div class="px-4 py-5 px-md-5 text-center text-lg-start" >
        <div class="container">
            <div class="row gx-lg-5 align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="my-5 display-3 fw-bold ls-tight">
                        登录到 <br />
                        <span class="text-primary">{{ $client->name }}</span>
                    </h1>
                    <div style="color: hsl(217, 10%, 50.8%);">
                        @if (!empty($client->description))
                            {{ $client->description }}
                            <br />
                        @endif
                        <hr />
                            客户端 ID: {{ $client->id }}<br />
                            客户端名称: {{ $client->name }}<br />
                            所属用户: {{ $client->user->id }}<br />
                    </div>
                </div>

                <div class="col-lg-6 mb-5 mb-lg-0">
                            <form id="passwordLoginForm" method="post" action="{{ route('login') }}">
                                @csrf
                                <div class="form-floating mb-2">
                                    <input type="text" class="form-control" placeholder="邮箱 / 手机号"
                                           aria-label="邮箱 / 手机号" id="account" name="account" required maxlength="25"
                                           value="{{ old('account') }}">
                                    <label>邮箱 / 手机号</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input type="password" class="form-control" placeholder="密码"
                                           aria-label="密码" name="password" id="password" required>
                                    <label>密码</label>
                                </div>

                                <p>如果您继续，则代表同意 <a class="link" target="_blank" href="{{ route('tos') }}">服务条款</a> 和 <a class="link" target="_blank" href="{{ route('privacy_policy') }}">隐私政策</a>。</p>
                                <button id="login-btn" type="submit" class="btn btn-primary">登录</button>

                            </form>

                        <div class="mt-3"></div>
                        <a class="link" href="{{ route('login.face-login') }}">
                            人脸登录
                        </a>
                        &nbsp;
                        @guest('web')
                            <a class="link" href="{{ route('register') }}">
                                {{ __('Register') }}
                            </a>
                            &nbsp;
                        @endguest
                        <a class="link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
