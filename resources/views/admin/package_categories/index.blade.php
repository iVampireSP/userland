@extends('layouts.admin')

@section('title', "套餐分类")

@section('content')
    <h2>分类</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.package_categories.create')}}">新建</a>

    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>名称</th>
            <th>短标签</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $category)
            <tr>
                <td>
                    {{ $category->name }}
                </td>
                <td>
                    {{ $category->slug }}
                </td>
                <td>
                    <a href="{{ route('admin.package_categories.edit', $category) }}">编辑</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
