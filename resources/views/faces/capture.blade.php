@extends('layouts.app')

@section('content')
    <h1 class="text-center">图像采集</h1>

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
    @endif



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
            stopVideo(video)
            started = false
            video.classList.add('d-none')
            startBtn.innerText = textCapture
            startBtn.classList.remove('btn-danger')
            startBtn.classList.add('btn-primary')
        }


        startBtn.addEventListener('click', async () => {
            if (started) {
                restoreBtn()
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
                validateForm.submit()
            })

        });
    </script>


@endsection
