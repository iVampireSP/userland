@extends('layouts.app')

@section('content')
    <h3>选择账户</h3>
    <p>这个会话包含了多个账户，请选择其中一个登录。</p>

    <div class="list-group" class="mt-3">

    @foreach($users as $user)
        <div
            user-id="{{$user->id}}"
            class="list-group-item list-group-item-action select-user" style="cursor: pointer">
            {{ $user->name }}
            <br />
            {{ $user->email }}
        </div>
    @endforeach
    </div>


    <form class="d-none" action="{{ route('login.switch') }}" method="post">
        @csrf
        <input type="hidden" name="user_id" id="user_id">
        <button type="submit" class="btn btn-primary">登录</button>
    </form>

    <script>
        document.querySelectorAll('.select-user').forEach(function (el) {
            el.addEventListener('click', function () {
                document.getElementById('user_id').value = el.getAttribute('user-id')

                document.querySelector('form').submit()
            })
        })
    </script>


@endsection
