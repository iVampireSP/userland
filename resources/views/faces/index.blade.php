@extends('layouts.app')

@section('content')
    @if(!$face)
        <h3>你还没有采集人脸。</h3>

        <p>要采集人脸，请点击下面的按钮。</p>

        <a href="{{ route('faces.capture')  }}" class="btn btn-primary">采集</a>
    @else
        <h3>您已采集</h3>

        <p>您可以点击下方按钮来测试。在登录时，您可以通过人脸来登录。</p>

        <a href="{{ route('faces.test')  }}" class="btn btn-primary">测试</a>

        <form class="d-inline" method="post" onsubmit="return confirm('确定删除吗？')" action="{{ route('faces.destroy') }}">
            @csrf
            @method('delete')
            <button type="submit" class="btn btn-danger">删除</button>
        </form>
    @endif
@endsection
