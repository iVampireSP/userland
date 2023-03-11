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
        <div class="form-floating mb-3">
            <input type="text" id="provider" name="provider" class="form-control"
                   placeholder="输入提供方">
            <label for="provider">提供方</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" name="personal_access_client"
                   id="personal_access_client">
            <label class="form-check-label" for="personal_access_client">
                个人访问客户端
            </label>
        </div>

        {{--    checkbox  password_client  --}}
        {{--    checkbox  personal_access_client  --}}
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" name="password_client"
                   id="password_client">
            <label class="form-check-label" for="password_client">
                密码访问客户端
            </label>
        </div>


        <button type="submit" class="btn btn-primary mt-3">新建</button>
    </form>

@endsection
