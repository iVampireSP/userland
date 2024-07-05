@extends('layouts.app')

@section('title', '换绑手机号')

@section('content')
    <h1>换绑手机号</h1>

    <form action="{{ route('phone.update') }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="phone">新手机号</label>
            <input type="text" class="form-control" id="phone" name="phone" placeholder="请输入手机号" value="{{ old('phone') }}">
        </div>
        <div class="mt-3"></div>
        <label for="code">验证码</label>

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
            const phone = document.getElementById('phone').value;
            if (! phone) {
                alert('请输入手机号');
                return;
            }
            // 发送验证码
            axios.post('{{route('phone.resend')}}', {
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
                alert('验证码发送失败');
            });
        });
    </script>

@endsection
