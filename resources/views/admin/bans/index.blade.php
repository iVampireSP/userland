@extends('layouts.admin')

@section('title', '用户封禁')

@section('content')

    <h1>用户: {{ $user->name }}</h1>


    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.bans.index', $user) }}" method="get">
                <div class="form-row row">
                    <div class="col-2">
                        <input type="text" class="form-control" name="code" placeholder="封禁代码"
                               value="{{ request('code') }}" aria-label="封禁代码">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-auto mt-3">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>代码</th>
            <th>应用</th>
            <th>封禁原因</th>
            <th>封禁时间</th>
            <th>解封时间</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($bans as $ban)
                <tr>
                    <td>
                        {{ $ban->id }}
                    </td>
                    <td>
                        {{ $ban->code }}
                    </td>
                    <td>
                        {{ $ban->client->name }}
                    </td>
                    <td>
                        {{ $ban->reason }}
                    </td>
                    <td>
                        {{ $ban->created_at }}
                    </td>
                    <td>
                        {{$ban->expired_at ?? '永久'}}
                    </td>
                    <td>
                        <a href="{{ route('admin.bans.edit', [$user, $ban]) }}">编辑</a>
                    </td>
                </tr>

            @endforeach
            </tbody>
        </table>
    </div>

    {{ $bans->links() }}

@endsection
