@extends('layouts.app')

@section('title', '微信绑定')

@section('content')
    <h2>您已绑定微信</h2>
    <p>如果您有需要，可以重新绑定。</p>

    <div class="mt-3 mb-3">
        <p>您可以使用 微信 扫描下方的二维码，关注公众号。或者可以搜索微信号 <code>{{ config('wechat.id') }}</code> 并关注。</p>
        <img src="https://open.weixin.qq.com/qr/code?username={{config('wechat.id')}}&style=1" style="width: 18rem" id="wechat-msg-login-qrcode" alt="微信公众号二维码"/>
    </div>

    <form action="{{ route('wechat.unbind') }}" method="post">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">解绑</button>
    </form>

@endsection
