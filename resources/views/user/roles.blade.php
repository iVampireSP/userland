@extends('layouts.app')

@section('title', "角色")

@section('content')
    <h2>角色</h2>
    <p>这个是为高级用户而设计的，如果您不清楚是什么，请联系管理员。</p>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>名称</th>
            <th>作用域</th>
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

            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $roles->links() }}

@endsection
