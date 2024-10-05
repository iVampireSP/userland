@extends('layouts.admin')

@section('title', "权限")

@section('content')
    <h2>权限</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.permissions.create')}}">新建</a>

    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>名称</th>
            <th>作用域</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($permissions as $permission)
            <tr>
                <td>
                    {{ $permission->name }}
                </td>
                <td>
                    {{ $permission->guard_name }}
                </td>
                <td>
                    <a href="{{ route('admin.permissions.edit', $permission) }}">编辑</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $permissions->links() }}

@endsection
