@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    <h3>{{ $user->name }}</h3>

    @if ($user->real_name_verified_at)
        <span class="text-success">实人验证于 {{ $user->real_name_verified_at }} </span>
        <br/>
    @endif

    <a href="{{ route('admin.users.show', $user) }}">作为 {{ $user->name }} 登录</a>

    @if ($user->banned_at)
        <p class="text-danger">已被全局封禁，原因: {{ $user->banned_reason }}</p>
    @else
        <a href="{{  route('admin.notifications.create') }}?user_id={{  $user->id }}">给此用户发送通知</a>
    @endif

    <a href="{{  route('admin.bans.index', $user) }}">应用程序封禁列表</a>

    <br/>

    <span>注册时间: {{ $user->created_at }}</span> <br/>

    <span>邮箱: {{ $user->email }} @if(!$user->hasVerifiedEmail())
            <small class="text-muted">没有验证</small>
        @endif</span> <br/>


    @if ($user->birthday_at)
        <p>
            生日: {{ $user->birthday_at->format('Y-m-d') }}
            <br/>
            {{ $user->birthday_at->age }} 岁，{{ $user->isAdult() ? '已成年' : '未成年' }}。
        </p>
    @endif

    {{--  账号操作  --}}
    <h3 class="mt-3">账号操作</h3>
    <form action="{{ route('admin.users.update', $user) }}" method="post">
        @csrf
        @method('PATCH')

        {{-- 封禁 --}}
        <div class="form-group">
            <label for="is_banned">封禁</label>
            <select class="form-control" id="is_banned" name="is_banned">
                <option value="0">否</option>
                <option value="1" @if ($user->banned_at) selected @endif>是</option>
            </select>
        </div>

        {{-- 原因 --}}
        <div class="form-group">
            <label for="banned_reason">封禁原因</label>
            <input type="text" class="form-control" id="banned_reason" name="banned_reason" placeholder="封禁原因"
                   value="{{ $user->banned_reason }}">
        </div>


        <div class="form-group">
            <label for="new_password">新的密码</label>
            <div class="input-group mb-3">
                <button class="btn btn-outline-secondary" type="button" id="new_password_btn">随机密码</button>
                <input id="new_password" type="password" name="password" class="form-control" placeholder="新的密码（留空不会设置）"
                       aria-label="新的密码（留空不会设置" aria-describedby="new_password_btn" autocomplete="false">
            </div>

        </div>


        <button type="submit" class="btn btn-primary mt-1">提交</button>
    </form>


    <h3 class="mt-3">实人认证信息</h3>
    <p>
        请注意自己的底线，不要随意改写及泄漏以下信息。
    </p>
    <div id="real_name_form">
        <form action="{{ route('admin.users.update', $user) }}" method="post">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="real_name">姓名</label>
                <input type="text" class="form-control" id="real_name" name="real_name" placeholder="姓名"
                       value="{{ $user->real_name }}" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="id_card">身份证号</label>
                <input type="text" class="form-control" id="id_card" name="id_card" placeholder="身份证号"
                       value="{{ $user->id_card }}" maxlength="18" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary mt-3">提交</button>
        </form>
    </div>

    <h3 class="mt-4">删除用户</h3>
    <p>
        这是个非常危险的操作，请三思而后行。
    </p>
    <form action="{{ route('admin.users.destroy', $user) }}" method="post">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('请再次确认要删除此用户吗？')">删除
        </button>
    </form>


    <style>
        #real_name_form {
            filter: blur(10px);
            transition: all 0.5s;
        }

        #real_name_form:hover {
            filter: blur(0);
        }
    </style>

    <script>
        document.getElementById('new_password_btn').addEventListener('click', function () {
            const new_password = document.getElementById('new_password');
            new_password.value = randomPassword(16);
            new_password.type = 'text';
        })


        // Random user password
        function randomPassword(length) {
            length = Number(length)
            // Limit length
            if (length < 6) {
                length = 6
            } else if (length > 16) {
                length = 16
            }
            let passwordArray = ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz', '1234567890', '!@#$%&*()'];
            let password = [];
            let n = 0;
            for (let i = 0; i < length; i++) {
                // If password length less than 9, all value random
                if (password.length < (length - 4)) {
                    // Get random passwordArray index
                    let arrayRandom = Math.floor(Math.random() * 4);
                    // Get password array value
                    let passwordItem = passwordArray[arrayRandom];
                    // Get password array value random index
                    // Get random real value
                    let item = passwordItem[Math.floor(Math.random() * passwordItem.length)];
                    password.push(item);
                } else {
                    // If password large then 9, lastest 4 password will push in according to the random password index
                    // Get the array values sequentially
                    let newItem = passwordArray[n];
                    let lastItem = newItem[Math.floor(Math.random() * newItem.length)];
                    // Get array splice index
                    let spliceIndex = Math.floor(Math.random() * password.length);
                    password.splice(spliceIndex, 0, lastItem);
                    n++
                }
            }
            return password.join("");
        }

    </script>

@endsection
