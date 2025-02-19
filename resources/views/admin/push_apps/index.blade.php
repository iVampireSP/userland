@extends('layouts.admin')
@section('title', "推送应用")
@section('content')
    <h2>推送应用</h2>
    <p>推送应用可以为不同的项目创建不同的推送应用，每个应用可以设置不同的推送配置。</p>

    <div class="mb-3">
        <a href="{{route('admin.push_apps.create')}}">新建</a>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Key</th>
                <th>状态</th>
                <th>最大连接数</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($apps as $app)
                <tr>
                    <td>{{ $app->id }}</td>
                    <td>{{ $app->key }}</td>
                    <td>
                        @if($app->enabled)
                            <span class="badge bg-success">启用</span>
                        @else
                            <span class="badge bg-danger">禁用</span>
                        @endif
                    </td>
                    <td>{{ $app->max_connections }}</td>
                    <td>
                        <div class="btn-group">
                            {{-- <a href="{{ route('admin.push_apps.show', $app) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a> --}}
                            <a href="{{ route('admin.push_apps.edit', $app) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.push_apps.destroy', $app) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除这个应用吗？')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $apps->links() }}

@endsection
