@extends('layouts.app')

@php($title = '令牌范围')

@section('title', $title)

@section('content')
    <h3>{{ $title }}</h3>
    <p>您可以在申请令牌时指定下方的 scope，可以拿到对应的用户数据。</p>

    @foreach($scopes as $scope)
        <span>{{ $scope->id }} - {{ $scope->description }}</span>
        <br />
    @endforeach

    <h3 class="mt-3">用户信息端点返回示例</h3>
    <p>请求地址: GET {{ route('openid.userinfo') }}</p>

    <pre>{
        "id": 1,
        "name": "test", // profile
        "email": "user@example.com",  // email
        "email_verified": true, // email, 邮箱验证状态，布尔值
        "real_name": "姓名", // realname
        "id_card": "身份证号", // realname
        "phone": "手机号", // phone
        "phone_verified": "身份证号", // phone, 手机号验证状态，布尔值
        "real_name_verified": false, // profile, 实名认证状态，布尔值
        "created_at": "2024-06-18T17:31:03.000000Z"
}
    </pre>

@endsection
