@extends('layouts.app')

@section('content')
    <h3>请求删除账号</h3>

    <p>如果您删除了账号，您使用 {{ config('app.display_name') }} 的所有数据将会被永久删除，您也可能无法使用依赖 {{ config('app.display_name') }} 的应用。</p>

    <form method="POST" action="{{ route('users.destroy') }}">
        @csrf
        @method('DELETE')

        <div class="form-floating mb-2">
            <input type="password" class="form-control" placeholder="当前密码"
                   aria-label="当前密码" name="password" required>
            <label>当前密码</label>
        </div>

        <button type="submit" class="btn btn-danger" onclick="confirm('请三思而后行。')">
            确认删除
        </button>
@endsection
