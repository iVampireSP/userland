@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>创建权限</h2>
                <a href="{{route('admin.permissions.index')}}">返回</a>

                <form action="{{ route('admin.permissions.store') }}" method="post" class="mt-3">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                               placeholder="标识">
                        <label for="name">标识（比如 custom-domain）</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" aria-label="作用域" name="guard">
{{--                            <option @selected(old('guard') == "global") value="global">全局</option>--}}

                            @foreach($guards as $g)
                                <option @selected(old('guard') == $g) value="{{$g}}">{{$g}}</option>
                            @endforeach
                        </select>
                        <label for="guard">作用域</label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">新建</button>
                </form>

                <p class="mt-3">权限的标识建议使用统一的格式，比如 "create-collection" 或者 "user.collection.create" </p>

            </div>
        </div>
    </div>
@endsection
