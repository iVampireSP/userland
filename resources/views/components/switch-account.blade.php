@if($type == \App\View\Components\SwitchAccount::TYPE_CONTAINER)
    <div>
        @if ($multiUserCount)
            <h3>切换账户</h3>
            <p>我们支持多账户会话，你可以在多个账户之间切换。</p>
        @else
            <h3>登录到其他账户</h3>
            <p>您可以登录到其他账号，新登录的账号将显示在此处。</p>
        @endif

        <div class="list-group" class="mt-3">
            @foreach($users as $u)
                <div
                    user-id="{{$u->id}}"
                    class="list-group-item list-group-item-action multiuser-switch-user " style="cursor: pointer">
                    {{ $u->name }}
                    <br />
                    {{ $u->email }}
                    <br />
                    @auth('web')
                        @if ($u->id == $user->id)
                            <span class="badge bg-primary">当前</span>
                        @endif
                    @endauth
                </div>
            @endforeach
        </div>

    </div>

@elseif($type == \App\View\Components\SwitchAccount::TYPE_DROPDOWN)
    @auth('web')
        @foreach($users as $u)
            <a user-id="{{$u->id}}" class="dropdown-item multiuser-switch-user @if(($u->id) == $user->id) active @endif" href="#"> {{ $u->name }}</a>
        @endforeach
    @else
        @foreach($users as $u)
            <a user-id="{{$u->id}}" class="dropdown-item multiuser-switch-user" href="#"> {{ $u->name }}</a>
        @endforeach
    @endauth
@elseif ($type == \App\View\Components\SwitchAccount::TYPE_ELEMENT)
    <form class="d-none" id="multiuser-switch-user-form" action="{{ route('login.switch') }}" method="post">
        @csrf
        <input type="hidden" name="user_id" id="user_id">
    </form>

    <script>
        document.querySelectorAll('.multiuser-switch-user').forEach(function (el) {
            el.addEventListener('click', function () {
                document.getElementById('user_id').value = el.getAttribute('user-id')
                document.querySelector('#multiuser-switch-user-form').submit()
            })
        })

        // remove highlight on login route
        const login_route = "{{ route('login') }}";
        const current_route = window.location.href;

        if (current_route === login_route) {
            document.querySelectorAll('.multiuser-switch-user').forEach(function (el) {
                el.classList.remove('active')
            });
        }
    </script>

@endif
