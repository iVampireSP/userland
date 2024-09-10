@extends('layouts.app')

@section('title', $client->name)

@section('content')

    <a href="{{ route('clients.index') }}" class="mb-3">
        返回
    </a>


    <h2>{{ $client->name }}</h2>

    <div class="mb-3">
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
    </div>

    <div class="input-group mb-3">
        <span class="input-group-text">应用程序 ID</span>
        <input aria-label="应用程序 ID" type="text" class="form-control" value="{{ $client->id }}" readonly>
    </div>

    @if (!empty($client->secret))
        <div class="input-group mb-3">
            <div class="input-group-text">
                {{ __('应用程序密钥') }} &nbsp;<input aria-label="应用程序密钥" type="checkbox"
                                                    id="secret-check-box"
                                                    data-secret="{{ $client->secret }}">
            </div>
            <input aria-label="勾选来查看" id="secret-input" type="text" class="form-control" readonly
                   placeholder="勾选来查看">
        </div>
    @endif



    <form class="d-contents" method="post" action="{{ route('clients.update', $client->id) }}">
        @method('PATCH')
        @csrf
        <h2>{{ __('设置') }}</h2>

        <div class="input-group mb-3">
            <span class="input-group-text">名称</span>
            <input aria-label="名称" type="text" class="form-control" name="name" placeholder="应用程序名称"
                   value="{{ $client->name }}">
        </div>


        {{--        <div class="input-group mb-3">--}}
        {{--            <span class="input-group-text">提供方</span>--}}
        {{--            <input aria-label="provider" type="text" name="provider" class="form-control"--}}
        {{--                   value="{{ $client->provider }}">--}}
        {{--        </div>--}}


        <div class="input-group mb-3">
            <span class="input-group-text">重定向地址</span>
            <input aria-label="重定向地址" type="text" class="form-control" name="redirect" placeholder="重定向地址"
                   value="{{ $client->redirect }}">
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text">描述</span>
            <input aria-label="描述" type="text" class="form-control" name="description" placeholder="将会显示在登录页面"
                   value="{{ $client->description }}">
        </div>

        @if(!empty($client->secret))
            <div class="input-group mb-3">
                <div class="input-group-text">
                    <input class="form-check-input" type="checkbox" value="1" name="reset_client_secret"
                           id="reset_client_secret" aria-label="重设应用程序密钥">
                </div>
                <span class="form-control">重设应用程序密钥（危险！你的应用程序密钥将会被立即重置！）</span>
            </div>
{{--            <div class="input-group mb-3">--}}
{{--                <div class="input-group-text">--}}
{{--                    <input class="form-check-input" type="checkbox" value="0" name="change_to_pkce"--}}
{{--                           id="change_to_pkce" aria-label="切换为 PKCE">--}}
{{--                </div>--}}
{{--                <span class="form-control">切换为 PKCE 应用程序（危险！原应用程序可能会立即停止工作）</span>--}}
{{--            </div>--}}
{{--        @else--}}
{{--            <div class="input-group mb-3">--}}
{{--                <div class="input-group-text">--}}
{{--                    <input class="form-check-input" type="checkbox" value="0" name="change_to_pkce"--}}
{{--                           id="change_to_pkce" aria-label="切换为 PKCE">--}}
{{--                </div>--}}
{{--                <span class="form-control">切换为授权码应用程序（危险！原应用程序可能会立即停止工作）</span>--}}
{{--            </div>--}}
        @endif

        {{--    令牌访问应用程序    --}}
        {{--        <div class="input-group mb-3">--}}
        {{--            <div class="input-group-text">--}}
        {{--                <input class="form-check-input" type="checkbox" value="1"--}}
        {{--                       @if($client->personal_access_client) checked @endif name="personal_access_client"--}}
        {{--                       id="personal_access_client" aria-label="是否是个人令牌访问应用程序">--}}
        {{--            </div>--}}
        {{--            <span class="form-control">是否是个人令牌访问应用程序</span>--}}
        {{--        </div>--}}

        {{--        <div class="input-group mb-3">--}}
        {{--            <div class="input-group-text">--}}
        {{--                <input class="form-check-input" type="checkbox" value="1"--}}
        {{--                       @if($client->password_client) checked @endif name="password_client"--}}
        {{--                       id="password_client" aria-label="是否是密码访问应用程序">--}}
        {{--            </div>--}}
        {{--            <span class="form-control">是否是密码访问应用程序</span>--}}
        {{--        </div>--}}

        {{--        <div class="input-group mb-3">--}}
        {{--            <div class="input-group-text">--}}
        {{--                <input class="form-check-input" type="checkbox" value="1"--}}
        {{--                       @if($client->personal_access_client) checked @endif name="personal_access_client"--}}
        {{--                       id="personal_access_client" aria-label="是否是个人令牌访问应用程序">--}}
        {{--            </div>--}}
        {{--            <span class="form-control">是否是个人令牌访问应用程序</span>--}}
        {{--        </div>--}}

        <button type="submit" class="btn btn-primary mt-3">
            更新
        </button>
    </form>


    <hr/>

    <form class="d-inline" method="post" action="{{ route('clients.destroy', $client->id) }}"
          onsubmit="return confirm('确定删除吗?')">
        @method('DELETE')
        @csrf
        <button type="submit" class="btn btn-danger mt-3">
            删除
        </button>

    </form>
    @if($client->trusted)
        <div class="mt-3">
            <h3>Application API 认证</h3>
            在调用 API 时，Header 中使用 <code>Authorization: Bearer {{ $client->id }}|{{ $client->secret }}</code> 来认证。
        </div>
    @endif

    <div class="mt-3">
        <h3>OpenID Connect 发现</h3>
        GET {{ route('openid.discovery') }}
    </div>

    <div class="mt-3">
        <h3>JWKs(JSON Web Key Sets) 端点</h3>
        GET {{ route('openid.jwks') }}
    </div>

    <div class="mt-3">
        <h3>授权端点</h3>
        GET {{ route('passport.authorizations.authorize') }}
    </div>

    <div class="mt-3">
        <h3>令牌端点</h3>
        POST {{ route('passport.token') }}
    </div>

    <div class="mt-3">
        <h3>用户信息端点</h3>
        GET {{ route('openid.userinfo') }}
    </div>



    <div class="mt-3">
        <h3>Token 有效时间</h3>
        令牌有效时间: {{config('passport.token_lifetime.token')}} 分钟 <br/>
        刷新令牌有效时间: {{config('passport.token_lifetime.refresh_token')}} 分钟
    </div>


    <script>
        let client_id = '{{ $client->id }}';


        @if (!empty($client->secret))
        let secretInput = document.getElementById("secret-input");
        let secretCheckBox = document.getElementById("secret-check-box");

        secretCheckBox.addEventListener('change', function () {
            if (this.checked) {
                secretInput.value = this.dataset.secret;
            } else {
                secretInput.value = '';
            }
        });
        @endif


    </script>
@endsection
