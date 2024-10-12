@extends('layouts.admin')

@section('title', "配额")

@section('content')
    <h2>配额</h2>
    <p>配额可以根据用户</p>

    <div class="mb-3">
        <a href="{{route('admin.quotas.create')}}">新建</a>
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>单位</th>
            <th>描述</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($quotas as $q)
            <tr>
                <td>
                    {{ $q->unit }}
                </td>
                <td>
                    {{ $q->description }}
                </td>
                <td>
                    <a href="{{ route('admin.quotas.edit', $q) }}">编辑</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $quotas->links() }}

@endsection
