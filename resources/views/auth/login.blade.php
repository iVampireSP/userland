@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center h-screen" style="height: 60vh">
        <div class="text-center">
            <span style="font-size: 10rem">
                <i class="bi bi-person-circle" id="main-icon" style="width: 120px"></i>
                <div id="img-container" class="d-none"></div>
            </span>

            <h2 id="form-title">注册 或 登录</h2>

            <form id="main-form" method="POST" onsubmit="return canSubmit()">
                @csrf

                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control mb-3 text-center" placeholder="邮箱"
                           aria-label="邮箱" required autofocus>
                </div>

                <div id="suffix-form"></div>
            </form>

            <br/>

            <a class="link" href="{{ route('password.request') }}">
                {{ __('Forgot Your Password?') }}
            </a>


        </div>
    </div>


    <div class="d-none">

        <div id="password-input">
            <div class="form-group mt-2">
                <input type="password" id="password" name="password"
                       class="form-control rounded-right text-center @error('password') is-invalid @enderror" required
                       placeholder="密码" aria-label="密码">
                @error('password')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                @enderror
            </div>
        </div>


        <div class="form-group mt-2" id="password-confirm-input">
            <label for="password-confirm">确认密码</label>
            <input type="password" id="password-confirm" name="password_confirmation"
                   class="form-control rounded-right" required autocomplete="new-password"
                   placeholder="再次输入您的密码">
        </div>

        <div id="remember-form">
            <input class="form-check-input" type="hidden" id="remember" name="remember" value="1">
        </div>

        <small id="tip" class="d-block"></small>

        <div class="mt-1" id="tos">如果您继续，则代表您已经阅读并同意 <a
                href="#" onclick="alert('测试阶段，还没写呢')"
                target="_blank"
                class="text-decoration-underline">服务条款</a>
        </div>

        <button class="btn btn-primary btn-block mt-2" type="submit" id="login-btn">
            继续
        </button>
    </div>


    <script>
        const gravatar_url = "https://cravatar.cn/avatar/"

        const login = "{{ route('login') }}"
        const register = "{{ route('register') }}"

        const mainIcon = document.getElementById('main-icon')
        const email = document.getElementById('email');
        const title = document.getElementById('form-title');
        const formSuffix = document.getElementById('suffix-form')
        const rememberForm = document.getElementById('remember-form')
        const passwordInput = document.getElementById('password-input')
        const passwordConfirmInput = document.getElementById('password-confirm-input')
        const loginBtn = document.getElementById('login-btn')
        const nameInput = document.getElementById('name')
        const mainForm = document.getElementById('main-form')
        const tos = document.getElementById('tos')
        const tip = document.getElementById('tip')
        const imgContainer = document.getElementById('img-container')

        @error('password')
            title.innerText = "注册 {{ config('app.display_name') }}"
        formSuffix.appendChild(rememberForm)
        @enderror
            @error('email')
            title.innerText = "密码错误"
        email.value = "{{ old('email') }}"
        formSuffix.appendChild(passwordInput)
        formSuffix.appendChild(rememberForm)
        formSuffix.appendChild(tos)
        formSuffix.appendChild(loginBtn)
        loginBtn.innerText = '登录'
        mainForm.action = login

        @enderror

        let canSubmit = function () {
            return (email.value !== '' && passwordInput.value !== '')
        }

        const validateUrl = "{{ route('login.exists-if-user') }}"

        email.onchange = function (ele) {
            const target = ele.target

            if (email.value === '') {
                title.innerText = "输入邮箱以继续"

                formSuffix.innerHTML = ''

                display_icon()
                mainIcon.classList.remove(...mainIcon.classList)
                mainIcon.classList.add('bi', 'bi-person-circle')

                return
            }

            formSuffix.innerHTML = ''
            formSuffix.appendChild(passwordInput)

            axios.post(validateUrl, {
                email: target.value
            })
                .then(function (res) {
                    mainForm.action = login

                    display_img(res.data['email_md5'])

                    title.innerText = "欢迎, " + res.data.name

                    formSuffix.appendChild(passwordInput)
                    formSuffix.appendChild(rememberForm)
                    formSuffix.appendChild(tos)
                    formSuffix.appendChild(loginBtn)
                    loginBtn.innerText = '登录'


                })
                .catch(function (err) {
                    mainForm.action = register

                    title.innerText = "注册 {{ config('app.display_name') }}"

                    display_img(err.response.data['email_md5'])

                    formSuffix.appendChild(passwordInput)
                    formSuffix.appendChild(tos)
                    formSuffix.appendChild(tip)

                    formSuffix.appendChild(loginBtn)

                    tip.innerText = '当您注册后，我们将为您分配随机用户名。'

                    loginBtn.innerText = '注册'
                });
        }



        function display_img(email_md5) {
            imgContainer.innerHTML = '<img alt="您的头像" src="' + gravatar_url  + '/' + email_md5 + '?size=256" width="120" class="rounded-circle" style="width: 10rem; height: 10rem">'
            imgContainer.classList.remove('d-none')
            mainIcon.classList.add('d-none')
        }

        function display_icon() {
            imgContainer.classList.add('d-none')
            mainIcon.classList.remove('d-none')
        }

    </script>

@endsection
