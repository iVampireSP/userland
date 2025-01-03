@extends('layouts.admin')

@section('title', '管理员登录')

@section('content')
    <div class="min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h4 class="mb-1">管理员登录</h4>
                                <p class="text-muted status-message">请插入并触摸 YubiKey</p>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form id="otpForm" method="post" action="{{ route('admin.login') }}" class="mb-4">
                                @csrf
                                <input type="password" id="otpInput" name="otp"
                                       class="form-control visually-hidden"
                                       autocomplete="off">

                                <div class="text-center">
                                    <div class="yubikey-container mb-4">
                                        <!-- 进度环 -->
                                        <svg class="progress-ring" viewBox="0 0 200 200">
                                            <circle class="progress-ring-circle-bg" cx="100" cy="100" r="90"/>
                                            <circle class="progress-ring-circle" cx="100" cy="100" r="90"/>
                                        </svg>

                                        <!-- Yubikey 设备 -->
                                        <div class="yubikey">
                                            <div class="body">
                                                <div class="button">
                                                    <i class="bi bi-shield-lock status-icon"></i>
                                                </div>
                                            </div>
                                            <div class="usb-connector"></div>
                                        </div>
                                    </div>

                                    <!-- 移动设备输入按钮 -->
                                    <button type="button" class="btn btn-outline-primary d-md-none"
                                            onclick="focusInput()">
                                        <i class="bi bi-keyboard me-2"></i>
                                        打开输入键盘
                                    </button>
                                </div>
                            </form>

                            <div class="mt-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-key-fill text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">使用说明</h6>
                                        <p class="text-muted small mb-0">
                                            PC端：直接触摸 YubiKey 按钮<br>
                                            移动端：点击输入按钮后触摸
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-shield-check text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">安全提示</h6>
                                        <p class="text-muted small mb-0">
                                            请确保设备已正确连接并等待按钮闪烁
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- 添加底部版权信息 -->
                            <div class="text-center mt-4">
                                <p class="text-muted small mb-0">
                                    Powered by <a href="https://leaflow.cc" class="text-decoration-none">Leaflow</a> &
                                    <a href="https://www.yubico.com" class="text-decoration-none">Yubico OTP</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .yubikey-container {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 0 auto;
        }

        /* 进度环样式 */
        .progress-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
            filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.1));
        }

        .progress-ring-circle-bg,
        .progress-ring-circle {
            fill: none;
            stroke-width: 4;
            stroke-linecap: round;
            transition: all 0.3s ease;
        }

        .progress-ring-circle-bg {
            stroke: var(--bs-gray-200);
        }

        .progress-ring-circle {
            stroke: #dc3545;
            stroke-dasharray: 565.48;
            stroke-dashoffset: 565.48;
            transition: stroke-dashoffset 0.1s linear, stroke 0.3s ease;
            transform-origin: center;
            filter: drop-shadow(0 0 1px rgba(0, 0, 0, 0.2));
        }

        /* Yubikey 样式 */
        .yubikey {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 100px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .yubikey .body {
            width: 100%;
            height: 85%;
            background: var(--bs-gray-800);
            border-radius: 8px 8px 0 0;
            position: relative;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-bottom: none;
        }

        /* USB 插口设计 */
        .yubikey .usb-connector {
            width: 70%;
            height: 15%;
            background: var(--bs-gray-800);
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 0 0 4px 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-top: none;
            transition: all 0.3s ease;
        }

        .yubikey .usb-connector::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 60%;
            background: var(--bs-gray-600);
            border-radius: 2px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }

        .yubikey .usb-connector::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 30%;
            background: var(--bs-gray-500);
            border-radius: 1px;
            transition: all 0.3s ease;
        }

        .yubikey .button {
            width: 30px;
            height: 30px;
            background: var(--bs-gray-200);
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .status-icon {
            font-size: 1rem;
            color: var(--bs-gray-600);
            transition: all 0.3s ease;
        }


        /* 状态样式 */
        .yubikey-container.waiting .progress-ring-circle {
            stroke: var(--bs-danger);
            stroke-dashoffset: 565.48;
        }

        .yubikey-container.inputting .progress-ring-circle {
            stroke-dashoffset: var(--progress-offset, 565.48);
        }

        .yubikey-container.ready .progress-ring-circle {
            stroke-dashoffset: 0;
            stroke: var(--bs-success);
        }

        .yubikey-container.verifying .progress-ring-circle {
            stroke: var(--bs-warning);
            animation: rotate-progress 1s linear infinite;
        }

        .yubikey-container.inputting .button {
            box-shadow: 0 0 15px var(--bs-primary-rgb, rgba(13, 110, 253, 0.5));
            animation: pulse-button 2s infinite;
        }

        .yubikey-container.ready .button {
            background: var(--bs-success);
        }

        .yubikey-container.ready .status-icon {
            color: var(--bs-white);
        }

        .yubikey-container.error .progress-ring-circle {
            stroke: var(--bs-danger);
            animation: shake-progress 0.5s ease;
        }

        .yubikey-container.error .button {
            background: var(--bs-danger);
        }

        /* 深色模式支持 */
        @media (prefers-color-scheme: dark) {
            .progress-ring-circle-bg {
                stroke: var(--bs-gray-700);
            }

            .yubikey .body {
                background: var(--bs-gray-700);
                border-color: var(--bs-gray-600);
            }

            .yubikey .usb-connector {
                background: var(--bs-gray-700);
                border-color: var(--bs-gray-600);
            }

            .yubikey .usb-connector::before {
                background: var(--bs-gray-600);
                border-color: var(--bs-gray-500);
            }

            .yubikey .usb-connector::after {
                background: var(--bs-gray-500);
            }

            .yubikey .button {
                background: var(--bs-gray-600);
                border-color: var(--bs-gray-500);
            }

            .status-icon {
                color: var(--bs-gray-300);
            }

        }

        @keyframes rotate-progress {
            from {
                transform: rotate(-90deg);
            }
            to {
                transform: rotate(270deg);
            }
        }

        @keyframes pulse-button {
            0% {
                box-shadow: 0 0 0 0 var(--bs-primary-rgb, rgba(13, 110, 253, 0.7));
            }
            70% {
                box-shadow: 0 0 0 10px var(--bs-primary-rgb, rgba(13, 110, 253, 0));
            }
            100% {
                box-shadow: 0 0 0 0 var(--bs-primary-rgb, rgba(13, 110, 253, 0));
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-1px);
            }
            75% {
                transform: translateX(1px);
            }
        }

        @keyframes shake-progress {
            0%, 100% {
                transform: rotate(-90deg);
            }
            25% {
                transform: rotate(-88deg);
            }
            75% {
                transform: rotate(-92deg);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 配置对象
            const CONFIG = {
                // 状态文本
                STATUS_TEXT: {
                    WAITING: '等待输入',
                    INPUTTING: '验证中...',
                    READY: '按回车确认',
                    VERIFYING: '正在验证...',
                    SUCCESS: '验证成功',
                    NETWORK_ERROR: '网络错误，请重试'
                },
                // 状态类名
                STATUS_CLASS: {
                    WAITING: 'waiting',
                    INPUTTING: 'inputting',
                    READY: 'ready',
                    VERIFYING: 'verifying',
                    ERROR: 'error'
                },
                // 颜色配置
                COLORS: {
                    DANGER: '#dc3545',
                    WARNING: '#ffc107',
                    PRIMARY: '#0d6efd',
                    SUCCESS: '#198754'
                },
                // 动画配置
                ANIMATION: {
                    TOTAL_LENGTH: 44,
                    CIRCLE_LENGTH: 565.48,
                    SPEED: 0.15,
                    RETREAT_SPEED: 0.08,
                    INPUT_TIMEOUT: 100,
                    ERROR_RESET_DELAY: 2000,
                    SUCCESS_REDIRECT_DELAY: 500
                }
            };

            const form = document.getElementById('otpForm');
            const input = document.getElementById('otpInput');
            const container = document.querySelector('.yubikey-container');
            const progressRing = container.querySelector('.progress-ring-circle');
            const csrfToken = document.querySelector('input[name="_token"]').value;

            let buffer = '';
            let isVerifying = false;
            let animationFrame = null;
            let currentProgress = 0;
            let targetProgress = 0;
            let isAnimating = false;
            let lastInputTime = 0;
            let hasCompleteInput = false;

            // 更新状态显示
            function updateStatus(status, text, progress = null) {
                container.className = `yubikey-container mb-4 ${CONFIG.STATUS_CLASS[status]}`;

                // 只在出错时更新状态消息
                const statusMessage = document.querySelector('.status-message');
                if (status === 'ERROR') {
                    statusMessage.textContent = text;
                } else {
                    statusMessage.textContent = '请插入并触摸 YubiKey';
                }

                if (progress !== null) {
                    targetProgress = progress;
                    startAnimation();
                }
            }

            function getProgressColor(progress) {
                if (progress < 0.3) return CONFIG.COLORS.DANGER;
                if (progress < 0.6) return CONFIG.COLORS.WARNING;
                if (progress < 1) return CONFIG.COLORS.PRIMARY;
                return CONFIG.COLORS.SUCCESS;
            }

            function showError(message) {
                if (animationFrame) {
                    cancelAnimationFrame(animationFrame);
                    isAnimating = false;
                }
                currentProgress = 0;
                targetProgress = 0;
                hasCompleteInput = false;
                buffer = '';
                input.value = '';  // 立即清空输入框
                updateStatus('ERROR', message);
                setTimeout(() => {
                    updateStatus('WAITING', '请插入并触摸 YubiKey', 0);
                }, CONFIG.ANIMATION.ERROR_RESET_DELAY);
                isVerifying = false;
            }

            // 快速输入处理
            function handleInput(char) {
                if (isVerifying) return;

                lastInputTime = Date.now();

                // 如果之前有错误，确保完全重置状态
                if (container.classList.contains(CONFIG.STATUS_CLASS.ERROR)) {
                    resetInput();
                    return;
                }

                buffer += char;

                if (buffer.length === CONFIG.ANIMATION.TOTAL_LENGTH) {
                    hasCompleteInput = true;
                    updateStatus('READY', CONFIG.STATUS_TEXT.READY, 1);
                    progressRing.style.stroke = CONFIG.COLORS.SUCCESS; // 立即设置为绿色
                } else if (buffer.length < CONFIG.ANIMATION.TOTAL_LENGTH) {
                    hasCompleteInput = false;
                    const progress = buffer.length / CONFIG.ANIMATION.TOTAL_LENGTH;
                    updateStatus('INPUTTING', CONFIG.STATUS_TEXT.INPUTTING, progress);
                } else {
                    buffer = buffer.slice(-CONFIG.ANIMATION.TOTAL_LENGTH);
                    hasCompleteInput = true;
                    updateStatus('READY', CONFIG.STATUS_TEXT.READY, 1);
                    progressRing.style.stroke = CONFIG.COLORS.SUCCESS; // 立即设置为绿色
                }
            }

            function resetInput() {
                buffer = '';
                hasCompleteInput = false;
                targetProgress = 0;
                updateStatus('WAITING', CONFIG.STATUS_TEXT.WAITING, 0);
            }

            // 动画循环
            function startAnimation() {
                if (isAnimating) return;
                isAnimating = true;

                function animate() {
                    const now = Date.now();

                    if (!hasCompleteInput && now - lastInputTime > CONFIG.ANIMATION.INPUT_TIMEOUT && targetProgress > 0) {
                        targetProgress = 0;
                        buffer = '';
                        updateStatus('WAITING', CONFIG.STATUS_TEXT.WAITING);
                    }

                    const diff = targetProgress - currentProgress;
                    const speed = diff > 0 ? CONFIG.ANIMATION.SPEED : CONFIG.ANIMATION.RETREAT_SPEED;

                    if (Math.abs(diff) < 0.01) {
                        currentProgress = targetProgress;
                        isAnimating = false;
                        return;
                    }

                    currentProgress += diff * speed;

                    const offset = CONFIG.ANIMATION.CIRCLE_LENGTH - (currentProgress * CONFIG.ANIMATION.CIRCLE_LENGTH);
                    progressRing.style.setProperty('--progress-offset', offset);

                    if (currentProgress > 0 && currentProgress < 1) {
                        const color = getProgressColor(currentProgress);
                        progressRing.style.stroke = color;
                    }

                    animationFrame = requestAnimationFrame(animate);
                }

                animate();
            }

            document.addEventListener('keypress', function (e) {
                if (isVerifying) return;

                if (e.key === 'Enter') {
                    if (buffer.length === CONFIG.ANIMATION.TOTAL_LENGTH) {
                        const otp = buffer;
                        if (validateYubicoOTP(otp)) {
                            submitForm(otp);
                        }
                    }
                    e.preventDefault();
                    return;
                }

                handleInput(e.key);
            });

            input.addEventListener('input', function () {
                if (isVerifying) return;

                const inputValue = this.value;
                const lastChar = inputValue[inputValue.length - 1] || '';

                if (lastChar === '\n' || lastChar === '\r') return;

                handleInput(lastChar);

                if (inputValue.length === CONFIG.ANIMATION.TOTAL_LENGTH) {
                    const otp = inputValue;
                    if (validateYubicoOTP(otp)) {
                        hasCompleteInput = true;
                        updateStatus('READY', CONFIG.STATUS_TEXT.READY, 1);
                    }
                }
            });

            function validateYubicoOTP(otp) {
                return otp.length === CONFIG.ANIMATION.TOTAL_LENGTH;
            }

            async function submitForm(otp) {
                if (isVerifying) return;
                isVerifying = true;

                input.value = otp;
                updateStatus('VERIFYING', CONFIG.STATUS_TEXT.VERIFYING);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({otp})
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        updateStatus('READY', CONFIG.STATUS_TEXT.SUCCESS);
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, CONFIG.ANIMATION.SUCCESS_REDIRECT_DELAY);
                    } else {
                        showError(data.error);
                    }
                } catch (error) {
                    showError(CONFIG.STATUS_TEXT.NETWORK_ERROR);
                }
            }
        });

        function focusInput() {
            document.getElementById('otpInput').focus();
        }
    </script>
@endsection
