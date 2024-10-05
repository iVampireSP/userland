@extends('layouts.admin')

@section('title', "套餐包")

@section('content')
    <h2>套餐包</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.packages.create')}}">新建套餐包</a>
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>标题</th>
            <th>标识</th>
            <th>分类</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($packages as $package)
            <tr>
                <td>
                    {{ $package->title }}
                </td>
                <td>
                    {{ $package->name }}
                    {{ $package->description }}
                </td>
                <td>
                    <a href="{{route('admin.package_categories.edit', $package->category)}}">{{ $package->category->name }}</a>
                </td>

                <td>
                    <a href="{{ route('admin.packages.edit', $package) }}">编辑</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $packages->links() }}

@endsection
