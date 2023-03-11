@extends('layouts.admin')

@section('title', "OAuth 客户端")

@section('content')

    <h2>OAuth 客户端</h2>

    <div class="mb-3">
        <a href="{{route('admin.clients.create')}}">新建客户端</a>
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>名称</th>
            <th>重定向</th>
            <th>提供方</th>
            <th>个人访问</th>
            <th>密码访问</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($clients as $client)
            <tr>
                <td>{{ $client->name }}</td>
                <td>{{ $client->redirect }}</td>
                <td>
                    @if ($client->trusted)
                        <span class="badge bg-success">受信任</span>
                    @endif

                    @if ($client->provider)
                        <span class="badge bg-primary">{{ $client->provider }}</span>
                    @endif
                </td>
                <td>
                    @if ($client->personal_access_client)
                        <span class="badge bg-primary">个人访问</span>
                    @endif
                </td>
                <td>
                    @if ($client->password_client)
                        <span class="badge bg-primary">密码访问</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn btn-sm btn-primary">编辑</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
