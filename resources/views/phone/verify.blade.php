@extends('layouts.app')

@section('title', '短信验证')

@section('content')
    <h1>短信验证</h1>
    <p>我们需要您确认接下来的操作。</p>

    <form action="{{ route('phone.validate') }}" method="post">
        @csrf

        <div class="input-group mb-3">
            <input type="text" class="form-control" name="code" id="code" placeholder="请输入验证码">
            <button class="btn btn-outline-secondary" type="button" id="button-send-code">发送验证码</button>
        </div>

        <button type="submit" class="btn btn-primary">提交</button>
    </form>


    <script>
        const sendCodeBtn = document.getElementById('button-send-code');
        let expired_at = new Date().getTime();

        sendCodeBtn.addEventListener('click', function () {
            // 发送验证码
            axios.post('{{route('phone.validate.send')}}').then(function (response) {
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
                alert('验证码发送失败');
            });
        });
    </script>

@endsection
