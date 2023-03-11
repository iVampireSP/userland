@extends('layouts.app')

@section('content')

    @php($user = auth('web')->user())

    @if ($user->real_name_verified_at)
        <x-alert-success>
            您已经完成实人认证。
        </x-alert-success>
    @else

        <x-alert-warning>
            由于实人认证接口费用高昂，我们需要收取 {{config('settings.supports.real_name.price')}} 元的费用来实名认证。
            <br/>
            人脸识别需要使用手机摄像头，请使用手机浏览器进行实人认证。
        </x-alert-warning>

        @if (!\Illuminate\Support\Facades\Cache::has('real_name:user:'.auth('web')->id()))

            <h3>支付 1 元来实名认证</h3>
            <p>在购买后，您必须在 24 小时内完成实名认证，否则次数将作废。</p>

            <form action="{{ route('real_name.pay') }}" method="post">
                @csrf

                <input type="radio" name="type" id="wechat" value="wxpay" checked>
                <label for="wechat"> <i class="bi bi-wechat"></i> 微信支付</label>

                <input type="radio" name="type" id="alipay" value="alipay">
                <label for="alipay"> <i class="bi bi-alipay"></i> 支付宝</label>

                <br />
                <button type="submit" class="btn btn-primary mt-3">支付 1 元</button>
            </form>
        @else

            <h3>实人认证</h3>

            {{--  if https --}}
            @if (request()->isSecure())
                <p>实名认证数据将全部加密传输，请放心实名。</p>
            @else
                <p class="text-danger">您的数据未加密传输，请使用 HTTPS 访问。</p>
            @endif

            <form action="{{ route('real_name.store') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="real_name" class="form-label">姓名</label>
                    <input required type="text" class="form-control" id="real_name" name="real_name"
                           placeholder="请输入您的姓名"
                           autocomplete="off" maxlength="6">
                </div>
                <div class="mb-3">
                    <label for="id_card" class="form-label">身份证号</label>
                    <input required type="text" class="form-control" id="id_card" name="id_card"
                           placeholder="请输入您的身份证号" autocomplete="off" maxlength="18">
                </div>
                <button type="submit" class="btn btn-primary">提交</button>
            </form>
        @endif

    @endif

@endsection

