@extends('layouts.app')

@section('title', '微信绑定')

@section('content')
    <h2>绑定微信</h2>

    <p>请使用 微信 扫描下方的二维码，关注公众号。或者可以搜索微信号 <code>{{ config('wechat.id') }}</code> 并关注。</p>
    <img style="width: 18rem" src="https://open.weixin.qq.com/qr/code?username={{config('wechat.id')}}&style=1" alt="公众号" />

    <p class="mt-3">随后，发送下方的代码并完成绑定。</p>

    <div>
        您的绑定代码是<code style="letter-spacing: 1rem;margin-left: 10px">{{$token}}</code>(注意大小写)
    </div>

    <p class="mt-3">每个代码的有效时间是 {{ $minutes }} 分钟，您需要在 <code id="countdown"></code> 内完成验证。为了使您更容易看清代码，我们为每个字符添加了间距，您在输入时无需添加空格或间距，但是请注意大小写。</p>

    <script>
        let minutes = {{ $minutes }};

        // 计算 minutes 后的时间
        let refreshTime = new Date(new Date().getTime() + minutes * 60 * 1000);

        setInterval(() => {
            let now = new Date();
            let time = refreshTime.getTime() - now.getTime();
            let minutes = Math.floor(time / 1000 / 60);
            let seconds = Math.floor(time / 1000 % 60);
            document.getElementById('countdown').innerHTML = `${minutes} 分 ${seconds} 秒`;

            if (time <= 0) {
                location.reload();
            }
        }, 100);

        setInterval(() => {
            axios.get('/').then((response) => {
                let data = response.data;

                if (data.wechat_open_id !== null) {
                    location.reload()
                }
            })
        }, 1000)
    </script>

@endsection
