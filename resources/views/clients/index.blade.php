@extends('layouts.app')

@section('content')

    <h2>客户端</h2>
    <p>您可以将您的客户端接入 {{ config('app.display_name') }}。</p>

    <div class="mb-3">
        <a href="{{route('clients.create')}}">新建客户端</a>
    </div>

    <div class="list-group" class="mt-3">
        @foreach($clients as $client)
            <a href="{{ route('clients.edit', $client->id) }}"
               class="list-group-item list-group-item-action">
                {{ $client->name }}

                <br/>
                {{ $client->redirect }}
                <br/>
                @if ($client->personal_access_client)
                    <span class="badge bg-primary">个人访问</span>
                @endif

                @if ($client->password_client)
                    <span class="badge bg-primary">密码访问</span>
                @endif
            </a>

        @endforeach

    </div>

@endsection
