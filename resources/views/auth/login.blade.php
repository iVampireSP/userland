@extends('layouts.app')

@section('content')
    <h3>登录</h3>

    <form onsubmit="canSubmit()" id="passwordLoginForm" method="post" action="{{ route('login') }}">
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
        <button id="login-btn" type="submit" class="d-none mt-3 btn btn-primary">登录</button>

    </form>
    <br/>


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

    <br />


    <script>
        const gravatar_url = "https://cravatar.cn/avatar/"

        const login = "{{ route('login') }}"
        const register = "{{ route('register') }}"


        const account = document.getElementById('account');
        const passwordInput = document.getElementById('password')
        const loginBtn = document.getElementById('login-btn')
        const imgContainer = document.getElementById('img-container')


        function canSubmit() {
            return !(account.value === '' || passwordInput.value === '');
        }



        account.oninput = toggleBtn
        passwordInput.oninput = (e) => {
            // must > 8
            if (e.target.value.length > 8) {
                toggleBtn()
            }
        }



        function toggleBtn() {
            if (account.value !== '' && passwordInput.value !== '') {
                loginBtn.classList.remove('d-none')
            } else {
                loginBtn.classList.add('d-none')
            }
        }



    </script>

@endsection
