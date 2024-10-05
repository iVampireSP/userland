@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>新建套餐包</h2>
                <a href="{{route('admin.packages.index')}}">返回</a>

                <form action="{{ route('admin.packages.store') }}" method="post" class="mt-3">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label">标题</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">描述</label>
                        <input type="text" name="description" id="description" value="{{ old('description') }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">内容</label>
                        <textarea name="content" id="content" class="form-control" value="{{ old('content') }}" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">标识名</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">分类</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $category->id) == $category->id) >{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="enable_quota" id="enable_quota"  class="form-check-input" @checked(old('enable_quota', true)) >
                        <label for="enable_quota" class="form-check-label">启用配额</label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">新建</button>
                </form>

            </div>
        </div>
    </div>
@endsection
