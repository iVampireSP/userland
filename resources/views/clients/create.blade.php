@extends('layouts.app')

@section('content')
    <h2>新建客户端</h2>

    <a href="{{route('clients.index')}}">客户端列表</a>

    <form action="{{ route('clients.store') }}" method="post" class="mt-3">
        @csrf
        <div class="form-floating mb-3">
            <input type="text" id="name" name="name" class="form-control" placeholder="输入客户端名称">
            <label for="name">名称</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" id="redirect" name="redirect" class="form-control"
                   placeholder="输入客户端重定向地址">
            <label for="redirect">重定向</label>
        </div>


        <p>如果您不了解，请勿勾选。</p>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" name="personal_access_client"
                   id="personal_access_client">
            <label class="form-check-label" for="personal_access_client">
                仅允许个人令牌访问
            </label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" name="password_client"
                   id="password_client">
            <label class="form-check-label" for="password_client">
                启用密码登录支持
            </label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">新建</button>
    </form>

@endsection
