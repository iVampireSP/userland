@extends('layouts.app')

@section('title', '个人访问密钥')

@section('content')

    @if (session('token'))
        <h3>保护好您的 Token</h3>
        <textarea aria-label="Token 区域" class="form-control mb-3" rows="5" readonly>{{ session('token') }}</textarea>
    @endif

    <h3>个人访问密钥</h3>

    <div class="mb-3">
        <a href="{{ route('tokens.create') }}">新建</a>
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>名称</th>
            <th>权限</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>


        @foreach($tokens as $token)
            <tr>
                <td>{{ $token->name }}</td>
                <td>
                    @foreach($token->scopes as $scope)
                        {{ $scope }}
                    @endforeach


                </td>
                <td>
                    <form method="post" action="{{ route('tokens.destroy', $token->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">删除</button>
                    </form>
                </td>
            </tr>

        @endforeach


        </tbody>
    </table>


    <table>
        <tr>

        </tr>

    </table>

@endsection
