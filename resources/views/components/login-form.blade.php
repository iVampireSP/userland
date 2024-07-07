<div>
    <ul class="nav nav-pills mb-3" id="login-method-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#login-method-password" type="button"
                    role="tab">账户密码
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#login-method-face" type="button" role="tab">
                面部扫瞄
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#login-method-sms" type="button" role="tab">
                短信验证码
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#login-method-wechat-msg" type="button" role="tab">
                微信口令
            </button>
        </li>
    </ul>
    <div class="tab-content" id="login-method-tabContent">
        <div class="tab-pane fade show active" id="login-method-password" role="tabpanel" tabindex="0">
            <form id="passwordLoginForm" class="recaptcha-form" method="post" action="{{ route('login') }}">
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

                <button type="submit" class="mt-3 btn btn-primary">登录</button>

            </form>
        </div>
        <div class="tab-pane fade" id="login-method-face" role="tabpanel" tabindex="1">
            <div class="row flex align-content-center w-100 align-items-center justify-content-center">
                <video id="face-capture" playsinline muted autoplay class="w-75 d-none"></video>
                <div class="text-center">
                    <p id="alert-success" class="text-success d-none">图像已采集，正在校验中，请勿离开。</p>
                    <p id="alert-failed" class="text-danger d-none">无法验证您的身份，您可以重新采集。</p>
                    <p id="alert-capture-failed" class="text-danger d-none">验证失败，您可以刷新页面或重启浏览器。</p>
                </div>

                <div class="mt-3 text-center">
                    <button class="btn btn-primary" id="start-record">采集</button>
                    <p class="text-info mt-3">在点击采集后，可能需要一段时间加载。</p>
                </div>
            </div>
            <form action="{{ route('login.face-login') }}" id="validate-form" class="recaptcha-form" method="post">
                @csrf
                <input type="hidden" name="image_b64" id="image-value">
                <input type="submit" class="d-none" id="face-login-submit-btn" />
            </form>
        </div>
        <div class="tab-pane fade" id="login-method-sms" role="tabpanel" aria-labelledby="pills-contact-tab"
             tabindex="0">
            <form action="{{ route('login.sms.validate') }}" class="recaptcha-form" method="post">
                @csrf
                <p>如果当前手机号没有注册，将会自动为您创建一个账号。</p>
                <div class="form-group">
                    <label for="phone">手机号</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="请输入手机号"
                           value="{{ old('phone') }}">
                </div>
                <div class="mt-3"></div>
                <label for="code">验证码</label>

                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="code" id="code" placeholder="请输入验证码">
                    <button class="btn btn-outline-secondary" type="button" id="button-send-code">发送验证码</button>
                </div>

                <button type="submit" class="btn btn-primary">登录 / 注册</button>
            </form>
        </div>

        <div class="tab-pane fade" id="login-method-wechat-msg" role="tabpanel" aria-labelledby="pills-contact-tab"
             tabindex="0">

            <p>使用微信扫码下方二维码，或者通过微信号搜索并关注公众号 {{ config('wechat.id') }}。</p>
            <img style="width: 18rem" id="wechat-msg-login-qrcode" alt="微信公众号二维码"/>

            <p>关注公众号后，向公众号发送小写字母 <code>t</code>，随后输入代码。</p>

            <form id="tokenLoginForm" method="post" action="{{ route('login.token') }}" class="recaptcha-form">
                @csrf
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" placeholder="口令代码"
                           name="token" required maxlength="16"
                           value="{{ old('token') }}">
                    <label>口令代码</label>
                </div>


                <button type="submit" class="mt-3 btn btn-primary">登录</button>

            </form>
        </div>
    </div>

    <p class="mt-3">如果您继续登录，则代表同意 <a class="link" target="_blank" href="{{ route('tos') }}">服务条款</a> 和 <a
            class="link" target="_blank" href="{{ route('privacy_policy') }}">隐私政策</a>。</p>

    <br/>


    @guest('web')
        <a class="link" id="sms-register" href="#">
            手机号注册
        </a>
        &nbsp;
        <a class="link" href="{{ route('register') }}">
            邮箱注册
        </a>
        &nbsp;
    @endguest
    <a class="link" href="{{ route('password.request') }}">
        {{ __('Forgot Your Password?') }}
    </a>

</div>


<script>
    const gravatar_url = "https://cravatar.cn/avatar/"

    const login = "{{ route('login') }}"
    const register = "{{ route('register') }}"


    const account = document.getElementById('account');
    const passwordInput = document.getElementById('password')
    const loginBtn = document.getElementById('login-btn')
    const imgContainer = document.getElementById('img-container')


</script>

<script>
    let start = null
    let stopVideo = null
    // on ready
    window.onload = function () {
        start = window.face_capture.start
        stopVideo = window.face_capture.stopVideo
    }


    const video = document.querySelector('#face-capture');
    const alertSuccess = document.querySelector('#alert-success');
    const alertFailed = document.querySelector('#alert-failed');
    const alertCaptureFailed = document.querySelector('#alert-capture-failed');
    const startBtn = document.querySelector('#start-record');
    const validateForm = document.querySelector('#validate-form');
    const imageValue = document.querySelector('#image-value');

    let started = false

    const textCapture = "采集"
    const textStop = "停止"


    function restoreBtn() {
        started = false
        stopVideo(video)
        video.classList.add('d-none')
        startBtn.innerText = textCapture
        startBtn.classList.remove('btn-danger')
        startBtn.classList.add('btn-primary')
    }


    startBtn.addEventListener('click', async () => {
        if (started) {
            restoreBtn()
            return;
        }

        started = true

        alertSuccess.classList.add('d-none')
        alertFailed.classList.add('d-none')
        alertCaptureFailed.classList.add('d-none')
        startBtn.innerText = textStop
        startBtn.classList.add('btn-danger')
        startBtn.classList.remove('btn-primary')


        video.classList.remove('d-none')
        start(video, (b64) => {
            video.classList.add('d-none')

            restoreBtn()

            alertSuccess.classList.remove('d-none')

            imageValue.value = b64
            // console.log(b64)
            // validateForm.submit()
            document.querySelector('#face-login-submit-btn').click()
        }, false)

    });

    const tabEl = document.querySelector('#login-method-tab')
    tabEl.addEventListener('shown.bs.tab', event => {
        if (event.relatedTarget.getAttribute('data-bs-target') === '#login-method-face') {
            if (started) {
                setTimeout(restoreBtn, 1000)
            }
        }
        if (event.target.getAttribute('data-bs-target') === '#login-method-face') {
            setTimeout(() => {
                startBtn.click()
            })
        }
        if (event.target.getAttribute('data-bs-target') === '#login-method-wechat-msg') {
            setTimeout(() => {
                const img = document.getElementById('wechat-msg-login-qrcode');
                img.src = "https://open.weixin.qq.com/qr/code?username={{config('wechat.id')}}"
            })
        }
    })

    document.getElementById('sms-register')?.addEventListener('click', function () {
        const b = document.querySelector('[data-bs-target="#login-method-sms"]');

        b.click()
    })
</script>

<script>
    const sendCodeBtn = document.getElementById('button-send-code');
    let expired_at = new Date().getTime();

    sendCodeBtn.addEventListener('click', function () {
        const phone = document.getElementById('phone').value;
        if (!phone) {
            alert('请输入手机号');
            return;
        }
        // 发送验证码
        axios.post('{{route('login.sms')}}', {
            phone: phone,
        }).then(function (response) {
            console.log(response);
            alert('验证码已发送');

            sendCodeBtn.disabled = true;
            expired_at = new Date().getTime() + {{config('settings.supports.sms.interval')}} * 1000;

            setInterval(function () {
                if (new Date().getTime() > expired_at) {
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.innerText = '重新发送';

                } else {
                    // 计算剩余时间
                    const remainingTime = Math.floor((expired_at - new Date().getTime()) / 1000);
                    sendCodeBtn.innerText = '重新发送 (' + remainingTime + 's)';
                }
            }, 100)

        }).catch(function (error) {
            console.log(error);
            alert('验证码发送失败, ' +  error.response.data.message);
        });
    });
</script>
