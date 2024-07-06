@extends('layouts.app')

@php($user = auth('web')->user())

@section('content')
    @if ($user->email)
        @if (!auth('web')->user()->hasVerifiedEmail())
            <div class="mb-3">
                <h3>验证邮箱</h3>
                <p>应用程序需要验证您的邮箱，请先<a href="{{ route('verification.notice') }}">验证您的邮箱</a>。</p>
            </div>
        @else
            <div class="mb-3">
                <h3>更改邮箱</h3>
                <p>
                    您可以更改您的邮箱，但您必须验证您的新邮箱地址。
                    <a href="{{ route('email.edit') }}">修改邮件地址</a>。
                </p>
            </div>
        @endif
    @else
        <div class="mb-3">
            <h3>绑定邮箱</h3>
            <p>
                部分应用程序需要您绑定电子邮件地址才能正常运行，请
                <a href="{{ route('email.edit') }}">绑定电子邮件</a>。
            </p>
        </div>
    @endif


    <h3>嗨, <span class="link" data-bs-toggle="modal" data-bs-target="#userInfo"
                  style="cursor: pointer">{{ auth('web')->user()->name }}</span></h3>
    @php($user = auth('web')->user())
    <form method="POST" action="{{ route('users.update') }}">
        @csrf
        @method('PATCH')
        <div class="form-floating mb-2">
            <input type="text" class="form-control" placeholder="用户名"
                   aria-label="用户名" name="name" required maxlength="25"
                   value="{{ $user->name }}">
            <label>用户名</label>
        </div>

        <button type="submit" class="btn btn-primary">
            更新
        </button>
    </form>

    <h3 class="mt-3">状态</h3>
    <div>
        可以使用此 API 来获取您的状态：GET <a href="{{ route('public.status.show', $user->id) }}"
                                             class="text-decoration-underline">{{ route('public.status.show', $user->id) }}</a>
    </div>
    <form method="POST" action="{{ route('status.update') }}">
        @csrf
        <div class="form-floating mb-2">
            <input type="text" class="form-control" placeholder="状态文本"
                   aria-label="状态" name="text" maxlength="25"
                   value="{{ $user->status?->text }}">
            <label>状态</label>
        </div>

        <button type="submit" class="btn btn-primary">
            更新
        </button>
    </form>


    <div class="modal fade" id="userInfo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>ID: {{ $user->id }}</p>
                    <p>Email: {{ $user->email }}</p>
                    @if ($user->birthday_at)
                        <p>年龄: {{ $user->birthday_at->age . ' 岁' }}</p>
                    @endif
                    <p>注册时间: {{ $user->created_at }}</p>
                    <p>验证时间: {{ $user->email_verified_at }}</p>
                    @if ($user->real_name_verified_at)
                        <p>实人认证时间: {{ $user->real_name_verified_at }}</p>
                    @endif
                    <p>
                        营销邮件订阅: <span class="user-select-none">
                            <a
                                onclick="update_receive_marketing_email()" style="cursor: pointer"
                                class="text-decoration-underline"></a>
                            <span id="receive_marketing_email_append_text"></span>
                        </span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">好
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>

        let receive_marketing_email = {{ $user->receive_marketing_email ? 'true' : 'false' }};
        let receive_marketing_email_append_text = document.querySelector('#receive_marketing_email_append_text');

        function update_receive_marketing_email_text() {
            let ele = document.querySelector('a[onclick="update_receive_marketing_email()"]');

            if (receive_marketing_email) {
                ele.innerText = '是';
                receive_marketing_email_append_text.innerText = '';
            } else {
                receive_marketing_email_append_text.innerText = '。创业不易，感谢理解。';
                ele.innerText = '否';
            }
        }

        function update_receive_marketing_email() {
            axios.patch("{{route('users.update')}}", {
                receive_marketing_email: !receive_marketing_email
            }).then(response => {
                receive_marketing_email = response.data['receive_marketing_email']

                update_receive_marketing_email_text(receive_marketing_email)
            }).finally(() => {
                update_receive_marketing_email_text()
            })
        }

        update_receive_marketing_email_text()

    </script>


    @if (!$user->isRealNamed())
        <div class="mt-3">
            <h3>实人认证</h3>
            <div class="mt-1">
                部分应用程序可能要求您<a href="{{ route('real_name.create') }}">实人认证</a>。
            </div>
        </div>
    @endif

    @if (!$user->isPhoneVerified())
        <div class="mt-3">
            <h3>验证并绑定手机号</h3>
            <div class="mt-1">
                部分应用程序可能要求您<a href="{{ route('phone.create') }}">验证手机号</a>。
            </div>
        </div>
    @else
        <div class="mt-3">
            <h3>已验证手机号</h3>
            <div class="mt-1">
                您已验证手机号，如果您有需要，可以查看<a href="{{ route('phone.create') }}">此页面</a>。
            </div>
        </div>
    @endif

    @if (!$user->hasWeChatOpenID())
        <div class="mt-3">
            <h3>绑定微信</h3>
            <div class="mt-1">
                绑定微信后，可以使用口令操作一些服务，<a href="{{ route('wechat.bind') }}">绑定</a>。
            </div>
        </div>
    @else
        <div class="mt-3">
            <h3>已绑定微信</h3>
            <div class="mt-1">
                您已绑定微信，如果您需要，可以<a href="{{ route('wechat.unbind') }}">更换绑定</a>。
            </div>
        </div>
    @endif

    <div class="mt-3">
        <x-switch-account />
    </div>

@endsection
