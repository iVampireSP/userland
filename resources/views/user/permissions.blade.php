@extends('layouts.app')

@section('title', "权限")

@section('content')
    <h2>你的权限</h2>
    <p>这里列出了当前用户所有的权限，如果您不清楚是什么，请联系管理员。</p>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>名称</th>
            <th>作用域</th>
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
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $permissions->links() }}

@endsection
