@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>新建分类</h2>
                <a href="{{route('admin.package_categories.index')}}">返回</a>

                <form action="{{ route('admin.package_categories.store') }}" method="post" class="mt-3">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                               placeholder="名称">
                        <label for="name">分类名</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" id="slug" name="slug" class="form-control" value="{{ old('name') }}"
                               placeholder="短标签">
                        <label for="slug">短标签</label>
                    </div>



                    <button type="submit" class="btn btn-primary mt-3">新建</button>
                </form>

            </div>
        </div>
    </div>
@endsection
