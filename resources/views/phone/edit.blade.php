@extends('layouts.app')

@section('title', '换绑 / 解绑')

@section('content')
    <h1>换绑 / 解绑</h1>
    <p>换绑后，一些依赖应用可能无法正常登录。</p>

    <form method="post" action="{{ route('phone.unbind') }}" onsubmit="return confirm('确定要解绑吗？')">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn btn-danger">解绑</button>
    </form>
@endsection
