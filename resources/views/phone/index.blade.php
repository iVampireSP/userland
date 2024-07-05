@extends('layouts.app')

@section('title', '已验证手机号')

@section('content')
    <h1>已验证手机号</h1>
    <p>您的手机号已成功验证，如果您有需要，可以解除绑定后换绑手机号。</p>

    <a href="{{ route('phone.edit') }}" class="btn btn-primary">换绑</a>
@endsection
