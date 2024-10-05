@extends('layouts.app')

@section('title', '订阅')

@section('content')
    <h3>订阅</h3>

    <div class="mb-3">
        <a href="{{ route('tokens.create') }}">新建 PAT</a>
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>方案</th>
            <th>到期时间</th>
            <th>创建时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($tokens as $token)
            <tr>
                <td>{{ $token->name }}</td>
                <td>
                    @foreach ($token->scopes as $scope)
                        {{ $scope }}
                    @endforeach
                </td>
                <td>
                    @if ($token->client)
                        {{ $token->client->name }}
                    @else
                        <span class="text-danger">未知</span>
                    @endif
                </td>
                <td>{{ $token->expires_at }}</td>
                <td>{{ $token->created_at }}</td>
                <td>
                    <form method="post" action="{{ route('tokens.destroy', $token->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">吊销</button>
                    </form>
                </td>
            </tr>
        @endforeach


        </tbody>
    </table>

    {{ $tokens->links() }}


    <form method="post" action="{{ route('tokens.destroy_all') }}" id="delete_all">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function delete_all() {
            if (confirm('确定删除所有吗？')) {
                document.getElementById('delete_all').submit();
            }
        }
    </script>

@endsection
