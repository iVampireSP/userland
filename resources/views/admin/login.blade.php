@extends('layouts.admin')
@section('title', '登录')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2>请插入并触摸设备</h2>
            <form id="passwordLoginForm" class="recaptcha-form" method="post" action="{{ route('admin.login') }}">
                @csrf
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" placeholder="一次性密钥"
                           aria-label="一次性密钥" name="otp" required maxlength="50">
                    <label>一次性密钥</label>
                </div>

                <p>你的密钥中包含了设备 ID，我们将通过设备 ID 来区分所属管理员用户。</p>
                <button type="submit" class="d-none mt-3 btn btn-primary">登录</button>
            </form>
        </div>
    </div>
@endsection
