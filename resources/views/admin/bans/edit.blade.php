@extends('layouts.admin')

@section('title', '编辑')

@section('content')
    <h1>正在编辑用户: {{ $user->name }} 的封禁</h1>

    <form method="POST" action="{{ route('admin.bans.update', [$user, $ban]) }}">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="code">封禁代码</label>
            <input type="text" class="form-control" id="code" name="code" placeholder="封禁代码"
                   value="{{ $ban->code }}" autocomplete="off">
        </div>

        <div class="form-group">
            <label for="reason">封禁原因</label>
            <input type="text" class="form-control" id="reason" name="reason" placeholder="封禁原因"
                   value="{{ $ban->reason }}" autocomplete="off">
        </div>

        <label for="expired_at">解封时间（留空将为永久封禁）</label>
        <div class="input-group" >
            <button class="btn btn-outline-secondary" type="button" onclick="document.querySelector('#expired_at').value = ''">切换为永久封禁</button>
            <input type="datetime-local" class="form-control" id="expired_at" name="expired_at" placeholder="解封时间（留空将为永久封禁）"
                   value="{{ $ban->expired_at }}" autocomplete="off">
        </div>

        <div class="form-group">
            <label for="pardoned">解除</label>
            <select class="form-control" id="pardoned" name="pardoned">
                <option value="0">否</option>
                <option value="1" @if ($ban->pardoned) selected @endif>是</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>

    <hr />

    {{--    删除封禁 --}}
    <form method="POST" action="{{ route('admin.bans.destroy', [$user, $ban]) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger mt-3">删除封禁</button>
        <p>删除封禁将会一并删除记录。如果您不想，请使用上方的立即解除。</p>
    </form>

@endsection
