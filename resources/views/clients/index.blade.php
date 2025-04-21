@extends('layouts.app')

@section('content')

    <h2>应用程序</h2>
    <p>您可以将您的应用程序接入 {{ config('app.display_name') }}。</p>

    <div class="mb-3">
        <a href="{{route('clients.create')}}">新建应用程序</a>
    </div>

    <div class="list-group" class="mt-3">
        @foreach($clients as $client)
            <a href="{{ route('clients.edit', $client->id) }}"
               class="list-group-item list-group-item-action">
                {{ $client->name }}

                @if($client->trusted)
                    <span class="badge bg-success">受信任</span>
                @endif

                @if ($client->provider)
                    <span class="badge bg-primary">{{ $client->provider }}</span>
                @endif

                <br/>
                {{ $client->redirect }}
                <br/>

                @if (empty($client->secret))
                    <span class="badge bg-primary">PKCE</span>
                @else
                    <span class="badge bg-primary">授权码</span>
                @endif

                @if ($client->personal_access_client)
                    <span class="badge bg-primary">个人令牌访问</span>
                @endif

                @if ($client->password_client)
                    <span class="badge bg-primary">密码访问</span>
                @endif
            </a>

        @endforeach

    </div>

    <div class="mt-3">
        {{ $clients->links() }}
    </div>

@endsection
