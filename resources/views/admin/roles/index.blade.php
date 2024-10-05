@extends('layouts.admin')

@section('title', "角色")

@section('content')
    <h2>角色</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.roles.create')}}">新建</a>
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
        @foreach($roles as $role)
            <tr>
                <td>
                    {{ $role->name }}
                </td>
                <td>
                    {{ $role->guard_name }}
                </td>
                <td>
                    <a href="{{ route('admin.roles.edit', $role) }}">编辑</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $roles->links() }}

@endsection
