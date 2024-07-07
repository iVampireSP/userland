@extends('layouts.app')

@section('title', '微信绑定')

@section('content')
    <h2>您已绑定微信</h2>
    <p>如果您有需要，可以重新绑定。</p>

    <img src="https://open.weixin.qq.com/qr/code?username={{config('wechat.id')}}&style=1" style="width: 18rem" id="wechat-msg-login-qrcode" alt="微信公众号二维码"/>

    <form action="{{ route('wechat.unbind') }}" method="post">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">解绑</button>
    </form>

@endsection
