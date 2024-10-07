@extends('layouts.admin')

@section('title', "用户角色")

@section('content')
    <h2>用户角色</h2>

    <div class="mb-3">
        <a href="{{route('admin.users.edit', $user)}}">返回</a>
        <a href="{{route('admin.users.permissions', $user)}}">权限</a>
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
        @foreach($roles as $role)
            <tr>
                <td>
                    {{ $role->name }}
                </td>
                <td>
                    {{ $role->guard_name }}
                </td>
                <td>
                    <form action="{{ route('admin.users.roles.toggle', [$user, $role]) }}"
                          method="post">
                        @csrf
                        @if ($user->roles->contains($role))
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

    {{ $roles->links() }}

@endsection
