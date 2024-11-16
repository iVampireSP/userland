@extends('layouts.app')

@section('content')
    <h2>注册</h2>

    <form action="{{ route('register') }}" method="POST" class="recaptcha-form">
        @csrf


        <div class="form-floating mb-2">
            <input type="text" class="form-control" placeholder="用户名"
                   aria-label="用户名" name="name" id="name" required>
            <label for="name">用户名</label>
        </div>

        <div class="form-floating mb-2">
            <input type="email" class="form-control" placeholder="邮箱"
                   aria-label="邮箱" name="email" id="email" required>
            <label for="email">邮箱</label>
        </div>


        <div class="form-floating mb-2">
            <input type="password" class="form-control" placeholder="密码"
                   aria-label="密码" name="password" id="password" required>
            <label for="password">密码</label>
        </div>

        <div class="form-floating mb-2">
            <input type="password" class="form-control" placeholder="确认密码"
                   aria-label="确认密码" name="confirm_password" id="confirm_password" required>
            <label for="confirm_password">确认密码</label>
        </div>


        <div class="text-start mt-3">如果您继续，则代表您已经阅读并同意
            <a
                href="{{ route('tos') }}"
                target="_blank"
                class="text-decoration-underline">服务条款</a>
            和
            <a
                href="{{route('privacy_policy')}}"
                target="_blank"
                class="text-decoration-underline">隐私政策</a>。

            <br />
            在您注册后，我们将给您发一份验证邮件。如果您 3 天内没有以任何方式验证账户（如手机号、邮箱等），您的账户将被删除。

        </div>


        <button class="btn btn-primary btn-block mt-2" type="submit">
            注册
        </button>

    </form>

    <br/>

    <a class="link" href="{{ route('login') }}">
        {{ __('Login') }}
    </a>
    &nbsp;
    <a class="link" href="{{ route('password.request') }}">
        {{ __('Forgot Your Password?') }}
    </a>
@endsection
