@extends('layouts.admin')

@section('title', "用户权限")

@section('content')
    <h2>用户的权限</h2>

    <div class="mb-3">
        <a href="{{route('admin.users.edit', $user)}}">返回</a>
        <a href="{{route('admin.users.roles', $user)}}">角色</a>
        <a href="{{route('admin.roles.index')}}">导航至角色</a>
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
                    <form action="{{ route('admin.users.permissions.toggle', [$user, $permission]) }}"
                          method="post">
                        @csrf
                        @if ($user->permissions->contains($permission))
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
