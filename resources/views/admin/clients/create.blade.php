@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>新建应用程序</h2>

                <a href="{{route('admin.clients.index')}}">应用程序列表</a>

                <form action="{{ route('admin.clients.store') }}" method="post" class="mt-3">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" id="name" name="name" class="form-control" placeholder="输入应用程序名称">
                        <label for="name">名称</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" id="redirect" name="redirect" class="form-control"
                               placeholder="输入应用程序重定向地址">
                        <label for="redirect">重定向</label>
                    </div>
{{--                    <div class="form-floating mb-3">--}}
{{--                        <input type="text" id="provider" name="provider" class="form-control"--}}
{{--                               placeholder="输入提供方">--}}
{{--                        <label for="provider">提供方</label>--}}
{{--                    </div>--}}

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="personal_access_client"
                               id="personal_access_client">
                        <label class="form-check-label" for="personal_access_client">
                            个人访问应用程序
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="password_client"
                               id="password_client">
                        <label class="form-check-label" for="password_client">
                            密码访问应用程序
                        </label>
                    </div>


                    <button type="submit" class="btn btn-primary mt-3">新建</button>
                </form>
            </div>
        </div>

@endsection
