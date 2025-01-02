@extends('layouts.admin')

@section('title', $client->name)

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <a href="{{ route('admin.clients.index') }}" class="mb-3">
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

                <div class="input-group mb-3">
                    <div class="input-group-text">
                        {{ __('应用程序密钥') }} &nbsp;<input aria-label="应用程序密钥" type="checkbox"
                                                              id="secret-check-box"
                                                              data-secret="{{ $client->secret }}">
                    </div>
                    <input aria-label="勾选来查看" id="secret-input" type="text" class="form-control" readonly
                           placeholder="勾选来查看">
                </div>

            </div>

            <div class="col-md-8">
                <form class="d-contents" method="post" action="{{ route('admin.clients.update', $client->id) }}">
                    @method('PATCH')
                    @csrf
                    <h2>{{ __('设置') }}</h2>

                    <div class="input-group mb-3">
                        <span class="input-group-text">名称</span>
                        <input aria-label="名称" type="text" class="form-control" name="name" placeholder="应用程序名称"
                               value="{{ $client->name }}">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">重定向地址</span>
                        <input aria-label="重定向地址" type="text" class="form-control" name="redirect"
                               placeholder="重定向地址"
                               value="{{ $client->redirect }}">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">描述</span>
                        <input aria-label="描述" type="text" class="form-control" name="description"
                               placeholder="将会显示在登录页面"
                               value="{{ $client->description }}">
                    </div>


                    {{--                    <div class="input-group mb-3">--}}
                    {{--                        <span class="input-group-text">提供方</span>--}}
                    {{--                        <input aria-label="provider" type="text" name="provider" class="form-control"--}}
                    {{--                               value="{{ $client->provider }}">--}}
                    {{--                    </div>--}}



                    {{--    密码访问应用程序    --}}
{{--                    <div class="input-group mb-3">--}}
{{--                        <div class="input-group-text">--}}
{{--                            <input class="form-check-input" type="checkbox" value="1"--}}
{{--                                   @if($client->personal_access_client) checked @endif name="personal_access_client"--}}
{{--                                   id="personal_access_client" aria-label="是否是个人访问应用程序">--}}
{{--                        </div>--}}
{{--                        <span class="form-control">是否是个人访问应用程序</span>--}}
{{--                    </div>--}}

                    <div class="input-group mb-3">
                        <div class="input-group-text">
                            <input class="form-check-input" type="checkbox" value="1"
                                   @if($client->password_client) checked @endif name="password_client"
                                   id="password_client" aria-label="是否是密码访问应用程序">
                        </div>
                        <span class="form-control">是否是密码访问应用程序</span>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-text">
                            <input class="form-check-input" type="checkbox" value="1" name="reset_client_secret"
                                   id="reset_client_secret" aria-label="重设应用程序密钥">
                        </div>
                        <span class="form-control">重设应用程序密钥（危险！你的应用程序密钥将会被立即重置！）</span>
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-text">
                            <input class="form-check-input" type="checkbox" value="1"
                                   @if($client->trusted) checked @endif name="trusted"
                                   id="trusted" aria-label="信任（将会自动授权）">
                        </div>
                        <span class="form-control">信任（将会自动授权）</span>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        更新
                    </button>
                </form>


                <hr/>

                <form class="d-inline" method="post" action="{{ route('admin.clients.destroy', $client->id) }}"
                      onsubmit="return confirm('确定删除吗?')">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger mt-3">
                        删除
                    </button>

                </form>

                <hr/>

{{--                @if ($client->secret)--}}
{{--                    @if($client->tenant_id)--}}
{{--                        <div class="mt-3">--}}
{{--                            <h3>禁用租户</h3>--}}
{{--                            <p>禁用租户后，不会影响现有的订阅。</p>--}}
{{--                        </div>--}}
{{--                        <form class="d-inline" method="post"--}}
{{--                              action="{{ route('admin.clients.tenant.disable', $client->id) }}"--}}
{{--                              onsubmit="return confirm('确定禁用租户吗?')">--}}
{{--                            @method('DELETE')--}}
{{--                            @csrf--}}
{{--                            <button type="submit" class="btn btn-danger mt-3">--}}
{{--                                禁用租户--}}
{{--                            </button>--}}

{{--                        </form>--}}
{{--                    @else--}}
{{--                        <div class="mt-3">--}}
{{--                            <h3>启用租户</h3>--}}
{{--                            <p>为该客户端启用订阅和计费功能</p>--}}
{{--                        </div>--}}
{{--                        <form class="d-inline" method="post"--}}
{{--                              action="{{ route('admin.clients.tenant.enable', $client->id) }}"--}}
{{--                              onsubmit="return confirm('确定启用?')">--}}
{{--                            @csrf--}}
{{--                            <button type="submit" class="btn btn-primary mt-3">--}}
{{--                                启用租户--}}
{{--                            </button>--}}

{{--                        </form>--}}
{{--                    @endif--}}
{{--                @endif--}}



                <div class="mt-3">
                    <h3>授权路由</h3>
                    {{ route('passport.authorizations.authorize') }}
                </div>

                <div class="mt-3">
                    <h3>请求令牌</h3>
                    {{ route('passport.token') }}
                </div>
            </div>


        </div>
    </div>

    <script>
        let client_id = '{{ $client->id }}';
        let secretInput = document.getElementById("secret-input");
        let secretCheckBox = document.getElementById("secret-check-box");

        secretCheckBox.addEventListener('change', function () {
            if (this.checked) {
                secretInput.value = this.dataset.secret;
            } else {
                secretInput.value = '';
            }
        });


    </script>
@endsection
