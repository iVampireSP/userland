@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>编辑 {{ $permission->name }}[{{$permission->guard_name}}]</h2>
                <a href="{{route('admin.permissions.index')}}">返回</a>

                <form action="{{ route('admin.permissions.update', $permission) }}" method="post" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-floating mb-3">
                        <input type="text" id="name" name="name" class="form-control" value="{{ $permission->name }}"
                               placeholder="标识">
                        <label for="name">标识（比如 custom-domain）</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" aria-label="作用域" name="guard">
{{--                            <option @selected($permission->guard_name == "global") value="global">全局</option>--}}

                            @foreach($guards as $g)
                                <option @selected($permission->guard_name == $g) value="{{$g}}">{{$g}}</option>
                            @endforeach
                        </select>
                        <label for="guard">作用域</label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">编辑</button>
                </form>


                <hr/>

                <form action="{{route('admin.permissions.destroy', $permission)}}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3">删除</button>
                </form>
            </div>
        </div>

@endsection
