@extends('layouts.app')

@section('title', $client->name)
@section('subtitle', $client->name)

@section('content')
<section>
    <div class="px-4 py-5 px-md-5 text-center text-lg-start" >
        <div class="container">
            <div class="row gx-lg-5 align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="my-5 display-3 fw-bold ls-tight">
                        登录到 <br />
                        <span class="text-primary">{{ $client->name }}</span>
                    </h1>
                    <div style="color: hsl(217, 10%, 50.8%);">
                        @if (!empty($client->description))
                            {{ $client->description }}
                            <br />
                        @endif
                        <hr />
                            客户端 ID: {{ $client->id }}<br />
                            客户端名称: {{ $client->name }}<br />
                            @if (!empty($client->user))
                                所属用户: {{ $client->user->name }}<br />
                            @endif
                    </div>
                </div>

                <div class="col-lg-6 mb-5 mb-lg-0">
                        <x-login-form />
                        &nbsp;
                        @guest('web')
                            <a class="link" href="{{ route('register') }}">
                                {{ __('Register') }}
                            </a>
                            &nbsp;
                        @endguest
                        <a class="link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
