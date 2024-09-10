@extends('layouts.app')

@section('content')
    <h2>新建应用程序</h2>

    <a href="{{route('clients.index')}}">应用程序列表</a>

    <form action="{{ route('clients.store') }}" method="post" class="mt-3">
        @csrf
        <div class="form-floating mb-3">
            <input type="text" id="name" name="name" class="form-control" placeholder="输入应用程序名称">
            <label for="name">名称</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" id="redirect" name="redirect" class="form-control"
                   placeholder="输入应用程序重定向地址">
            <label for="redirect">重定向地址</label>
        </div>


        <p>如果您不了解，请勿勾选。</p>
{{--        <div class="form-check">--}}
{{--            <input class="form-check-input" type="checkbox" value="1" name="personal_access_client"--}}
{{--                   id="personal_access_client">--}}
{{--            <label class="form-check-label" for="personal_access_client">--}}
{{--                仅允许个人令牌访问--}}
{{--            </label>--}}
{{--        </div>--}}

{{--        <div class="form-check">--}}
{{--            <input class="form-check-input" type="checkbox" value="1" name="password_client"--}}
{{--                   id="password_client">--}}
{{--            <label class="form-check-label" for="password_client">--}}
{{--                启用密码登录支持--}}
{{--            </label>--}}
{{--        </div>--}}
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" name="pkce_client"
                   id="pkce_client">
            <label class="form-check-label" for="pkce_client">
                Proof Key for Code Exchange (PKCE)
            </label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">新建</button>
    </form>

@endsection
