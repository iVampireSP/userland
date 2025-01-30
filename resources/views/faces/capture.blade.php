@extends('layouts.app')

@section('content')
    <style>
        .video-container {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            background: #000;
            width: 100%;
            max-width: 640px;
            aspect-ratio: 4/3;
            margin: 0 auto;
        }

        #face-capture {
            position: relative;
            z-index: 2;
            background: #000;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        body.flash-red { background-color: #ff0000 !important; }
        body.flash-green { background-color: #00ff00 !important; }
        body.flash-blue { background-color: #0000ff !important; }
        body.flash-yellow { background-color: #ffff00 !important; }
        body.flash-purple { background-color: #ff00ff !important; }
    </style>

    <h1 class="text-center">图像采集</h1>

    <div class="row flex align-content-center w-100 align-items-center justify-content-center position-relative">
        <div class="video-container">
            <video id="face-capture" playsinline muted autoplay style="visibility: hidden;"></video>
        </div>
        <div class="text-center mt-3">
            <p id="action-prompt" class="text-primary mb-3 h4 fw-bold"></p>
            <p id="verification-status" class="text-info mb-3 h5 d-none">正在进行身份验证...</p>
            <p id="light-prompt" class="text-warning mb-3 h5 d-none">请保持面部在摄像头中，注意观察屏幕变化</p>
            <p id="alert-success" class="text-success d-none">图像已采集，正在校验中，请勿离开</p>
            <p id="alert-failed" class="text-danger d-none">无法验证您的身份，您可以重新采集</p>
            <p id="alert-capture-failed" class="text-danger d-none">验证失败，您可以刷新页面或重启浏览器</p>
        </div>

        <div class="mt-3 text-center">
            <button class="btn btn-primary" id="start-record">开始采集</button>
            <p class="text-info mt-3">在点击采集后，需要完成动作验证和光线变化检测。请确保光线充足，面部清晰可见，并按照提示完成相应动作。</p>
        </div>
    </div>

    <small class="mt-4 d-block text-center">
        在您进行人脸图像采集时，我们会利用机器学习技术来识别您的面部特征，并通过人脸识别技术进行身份确认。为了确保是真人操作，您需要完成一些简单的动作并观察屏幕变化。成功录入后，我们会保存您的一张图像，以便在更新模型时重新提取特征。如果您希望删除录入的图像和人脸特征，只需在识别页面点击"删除"即可。如果您不想进行人脸采集，<a href="/">离开此页面</a>。
    </small>

    @if ($type == "store")
        <form action="{{ route('faces.capture') }}" id="validate-form" method="post">
            @csrf
            <input type="hidden" name="image_b64" id="image-value">
        </form>
    @elseif ($type == "test")
        <form action="{{ route('faces.test') }}" id="validate-form" method="post">
            @csrf
            <input type="hidden" name="image_b64" id="image-value">
        </form>
    @elseif ($type == "login")
        <form action="{{ route('login.face-login') }}" id="validate-form" method="post">
            @csrf
            <input type="hidden" name="image_b64" id="image-value">
        </form>
    @endif

    <script>
        let start = null;
        let stopVideo = null;
        let initializationInterval = null;

        function initializeFaceCapture() {
            if (window.face_capture) {
                clearInterval(initializationInterval);
                start = window.face_capture.start;
                stopVideo = window.face_capture.stopVideo;
                console.log('人脸识别模块初始化成功');
            }
        }

        // 等待face_capture模块加载
        initializationInterval = setInterval(initializeFaceCapture, 100);

        const video = document.querySelector('#face-capture');
        const alertSuccess = document.querySelector('#alert-success');
        const alertFailed = document.querySelector('#alert-failed');
        const alertCaptureFailed = document.querySelector('#alert-capture-failed');
        const startBtn = document.querySelector('#start-record');
        const validateForm = document.querySelector('#validate-form');
        const imageValue = document.querySelector('#image-value');

        let started = false;

        const textCapture = "开始采集";
        const textStop = "停止采集";

        function restoreBtn() {
            if (stopVideo) {
                stopVideo(video);
            }
            started = false;
            video.style.visibility = 'hidden';
            startBtn.innerText = textCapture;
            startBtn.classList.remove('btn-danger');
            startBtn.classList.add('btn-primary');
        }

        startBtn.addEventListener('click', async () => {
            if (!start || !stopVideo) {
                alert('人脸识别模块还未加载完成，请稍候...');
                return;
            }

            if (started) {
                restoreBtn();
                return;
            }

            started = true;

            alertSuccess.classList.add('d-none');
            alertFailed.classList.add('d-none');
            alertCaptureFailed.classList.add('d-none');
            startBtn.innerText = textStop;
            startBtn.classList.add('btn-danger');
            startBtn.classList.remove('btn-primary');

            video.style.visibility = 'visible';
            start(video, (b64) => {
                video.style.visibility = 'hidden';
                restoreBtn();
                alertSuccess.classList.remove('d-none');
                imageValue.value = b64;
                validateForm.submit();
            }, false);
        });
    </script>
@endsection
