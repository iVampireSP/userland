@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>编辑 {{ $packageCategory->name }}</h2>
                <a href="{{route('admin.package_categories.index')}}">返回</a>

                <form action="{{ route('admin.package_categories.update', $packageCategory) }}" method="post" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-floating mb-3">
                        <input type="text" id="name" name="name" class="form-control" value="{{ $packageCategory->name }}"
                               placeholder="名称">
                        <label for="name">分类名</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" id="slug" name="slug" class="form-control" value="{{ $packageCategory->slug }}"
                               placeholder="短标签">
                        <label for="slug">短标签</label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">编辑</button>
                </form>


                <hr/>

                <form action="{{route('admin.package_categories.destroy', $packageCategory)}}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3">删除</button>
                </form>
            </div>
        </div>

@endsection
