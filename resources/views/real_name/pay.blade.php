@extends('layouts.app')

@section('title', '付款')

@section('content')

    <style>
        .success-icon {
            font-size: 10rem;
            color: #096dff
        }
    </style>


    <div class="d-flex justify-content-center align-items-center h-screen" style="height: 60vh">
        <div class="text-center">
            <div id="pay">
                @php
                    if ($type === 'alipay') {
                        $type = '支付宝';
                    } elseif ($type === 'wxpay') {
                        $type = '微信';
                    } else {
                        $type = '相应的软件';
                    }
                @endphp

                <h3>请使用 "{{ $type }}" 扫描二维码。</h3>


                <div class="mt-3">
                    {{ $qrcode }}
                </div>

                <div class="mt-3">
                    在支付完成之前请不要关闭页面。
                </div>

            </div>

            <div class="d-none" id="pay-success">
                <div class="success-icon">
                    <i class="bi bi-check2-all"></i>
                </div>

                <h2>您已支付</h2>
                <p>现在您可以继续实名认证了。</p>
            </div>
            <div class="text-danger d-none" id="pay-error">此支付出现了问题，请联系我们。
            </div>
        </div>
    </div>


    <script>
        let waiting = false
        const inter = setInterval(function () {
            if (waiting) {
                return
            }
            waiting = true

            axios.get(location.href)
                .then(function (response) {
                    waiting=false

                    if (response.data.code === 1) {
                        document.getElementById('pay-success').classList.remove('d-none');
                        document.getElementById('pay').classList.add('d-none');

                        clearInterval(inter);

                        @auth
                        setTimeout(function () {
                            location.href = '/real_name';
                        }, 3000);
                        @endauth

                    }
                })
                .catch(function () {
                    document.getElementById('pay-error').classList.remove('d-none');
                    document.getElementById('pay').classList.add('d-none');

                    clearInterval(inter);
                    waiting=false

                });
        }, 1500);
    </script>

@endsection
