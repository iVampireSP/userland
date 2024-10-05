@extends('layouts.admin')

@section('title', "套餐角色")

@section('content')
    <h2>套餐包含的角色</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.packages.edit', $package)}}">返回</a>
        <a href="{{route('admin.packages.permissions', $package)}}">权限</a>
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
                    <form action="{{ route('admin.packages.roles.toggle', [$package, $role]) }}"
                          method="post">
                        @csrf
                        @if ($package->roles->contains($role))
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
