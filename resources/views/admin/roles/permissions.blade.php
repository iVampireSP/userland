@extends('layouts.admin')

@section('title', "角色权限")

@section('content')
    <h2>角色权限</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.roles.edit', $role)}}">返回</a>
        <a href="{{route('admin.permissions.index')}}">导航至权限</a>
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
                    <form action="{{ route('admin.roles.permissions.toggle', [$role, $permission]) }}"
                          method="post">
                        @csrf
                        @if ($role->permissions->contains($permission))
                            <button type="submit" class="btn btn-sm btn-danger">取消绑定</button>
                        @else
                            <button type="submit" class="btn btn-sm btn-primary">绑定</button>
                        @endif
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $permissions->links() }}

@endsection
