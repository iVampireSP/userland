@extends('layouts.app')

@section('content')

@php($user = auth('web')->user())

@if ($user->real_name_verified_at)
<x-alert-success>
    您已经完成实人认证。
</x-alert-success>
@else
<x-alert-warning>
    认证提供方会向我方收取一定费用，我们需要收取 {{config('settings.supports.real_name.price')}} 元的费用来实人认证。
    <br />
    人脸识别需要使用摄像头，我们建议您使用手机浏览器进行实人认证。
</x-alert-warning>

@if (!\Illuminate\Support\Facades\Cache::has('real_name:user:' . auth('web')->id()))

<h3>支付 1 元来实名认证</h3>
<p>在购买后，您必须在 24 小时内完成实名认证，否则次数将作废。</p>

<form action="{{ route('real_name.pay') }}" method="post">
    @csrf

    <input type="radio" name="type" id="wechat" value="wxpay" checked>
    <label for="wechat"> <i class="bi bi-wechat"></i> 微信支付</label>

    <input type="radio" name="type" id="alipay" value="alipay">
    <label for="alipay"> <i class="bi bi-alipay"></i> 支付宝</label>

    <br />
    <button type="submit" class="btn btn-primary mt-3">支付 {{config('settings.supports.real_name.price')}} 元</button>
</form>
@else

<h3>实人认证</h3>

<x-alert-warning>
    为了防止恶意注册，您的年龄必须大于 {{ config('settings.supports.real_name.min_age') }}
    岁，小于 {{ config('settings.supports.real_name.max_age') }} 岁，否则无法进行实人认证。
</x-alert-warning>


{{-- if https --}}
@if (request()->isSecure())
<p>实名认证数据将全部加密传输，请放心实名。</p>
@else
<p class="text-danger">您的数据未加密传输，请使用 HTTPS 访问。</p>
@endif

<form action="{{ route('real_name.store') }}" method="post">
    @csrf
    <div class="mb-3">
        <label for="real_name" class="form-label">姓名</label>
        <input required type="text" class="form-control" id="real_name" name="real_name" placeholder="请输入您的姓名" autocomplete="off" maxlength="6">
    </div>
    <div class="mb-3 has-validation">
        <label for="id_card" class="form-label">身份证号</label>
        <input required type="text" class="form-control" id="id_card" name="id_card" placeholder="请输入您的身份证号" autocomplete="off" maxlength="18">
    </div>

    <!-- error msg -->
    <p class="text-danger" id="error"></p>
    <button type="submit" id="submit" class="btn btn-primary" disabled="disabled">提交</button>
</form>




<script>
    function setErrorMsg(msg) {
        let e = document.getElementById("error")
        if (!msg) {
            e.innerText = "";
            return;
        }

        e.innerText = msg;
    }

    function validate(idCard) {
        if (!idCard) {
            setErrorMsg("要继续，请输入身份证号。");
            return false;
        }

        return isIdCard(idCard);
    }


    function isIdCard(idCard) {
        let regIdCard = /^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/;
        if (regIdCard.test(idCard)) {
            if (idCard.length === 18) {
                let idCardWi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
                let idCardY = [1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2];
                let idCardWiSum = 0;
                for (let i = 0; i < 17; i++) {
                    idCardWiSum += idCard.substring(i, i + 1) * idCardWi[i];
                }
                let idCardMod = idCardWiSum % 11;
                let idCardLast = idCard.substring(17);
                if (idCardMod === 2) {
                    if (idCardLast == "X" || idCardLast == "x") {
                        setErrorMsg(null);
                        return true;
                    } else {
                        setErrorMsg("末尾校验码计算错误，请检查身份证号。")
                        return false;
                    }
                } else {
                    if (idCardLast == idCardY[idCardMod]) {
                        setErrorMsg(null);
                        return true;
                    } else {
                        setErrorMsg("校验码与身份证不匹配，请检查身份证号。")
                        return false;
                    }
                }
            }
        } else {
            setErrorMsg("身份证格式不正确。")

            return false;
        }
    }


    let idInput = document.getElementById('id_card');
    idInput.addEventListener('input', function(e) {
        let value = e.target.value;

        let r = validate(value);

        document.getElementById("submit").disabled = !r;
    });
</script>

@endif

@endif


@endsection
