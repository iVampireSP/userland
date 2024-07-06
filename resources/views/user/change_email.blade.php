@extends('layouts.app')

@php($user = auth('web')->user())

@section('title', $user->email ? '修改邮箱' : '绑定邮箱')

@section('content')

    <h2>{{ $user->email ? '修改邮箱' : '绑定邮箱' }}</h2>
    <p>在您输入新的邮件地址后，我们将会向新的邮件地址发送一封邮件，请于 24 小时内完成验证。<br />如果新的邮件地址绑定了其他账户，原账户将会被解除绑定邮件地址。</p>

    @if ($user->email)
        <p>当前邮箱：{{ $user->email }}</p>
    @endif

    <form action="{{ route('email.edit') }}" method="post">
        @csrf

        <div class="form-group">
            <label for="email">新邮箱</label>
            <input type="email" class="form-control" name="email" id="email" placeholder="请输入新的邮箱地址" required>
        </div>

        <button type="submit" class="btn btn-primary mt-3">修改</button>
    </form>

@endsection
