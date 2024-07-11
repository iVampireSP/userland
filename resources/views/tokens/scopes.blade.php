@extends('layouts.app')

@php($title = '令牌范围')

@section('title', $title)

@section('content')
    <h3>{{ $title }}</h3>
    <p>您可以在申请令牌时指定下方的 scope，可以拿到对应的用户数据。</p>

    @foreach($scopes as $scope)
        <span>{{ $scope->id }} - {{ $scope->description }}</span>
        <br />
    @endforeach

    <h3 class="mt-3">用户信息端点返回示例</h3>
    <p>请求地址: GET {{ route('openid.userinfo') }}</p>

    <pre>{
  "id": 1,
  "name": "test", // profile
  "email": "user@example.com",  // email
  "email_verified": true, // email, 邮箱验证状态，布尔值
  "real_name": "姓名", // realname
  "id_card": "身份证号", // realname
  "phone": "手机号", // phone
  "phone_verified": "身份证号", // phone, 手机号验证状态，布尔值
  "real_name_verified": false, // profile, 实名认证状态，布尔值
  "created_at": "2024-06-18T17:31:03.000000Z"
}
    </pre>

    <h3 class="mt-3">Access Token 返回示例</h3>
    <p>如有需要，你可以参考 <a href="https://docs.authing.cn/v2/concepts/access-token-vs-id-token.html" class="link-primary" target="_blank">Access Token 和 Id Token</a>。</p>
    <p>绝对不要使用 Access Token 做认证。Access Token 本身不能标识用户是否已经认证。Access Token 中只包含了用户 id，在 sub 字段。在你开发的应用中，应该将 Access Token 视为一个随机字符串，不要试图从中解析信息。</p>
    <pre>{
  "aud": "4", // 你的应用 ID
  "jti": "13c65d8ab8efcbde7e91ceec1501efaa574de25f4e9c6da98ed3b2d03d90f8b33acf2ba1db2c6e48",
  "iat": 1720703114.050113,
  "nbf": 1720703114.050114,
  "exp": 1720704914.025737,
  "sub": "1",
  "scopes": [
    "profile",
    "email",
    "phone"
  ],
  "custom": "myCustomClaim"
}
    </pre>

    <h3 class="mt-3">ID Token 返回示例</h3>
    <p>Id Token 的格式为 JWT。ID Token 仅适用于认证场景。例如，有一个应用使用了谷歌登录，然后同步用户的日历信息，谷歌会返回 ID Token 给这个应用，ID Token 中包含用户的基本信息（用户名、头像等）。应用可以解析 ID Token 然后利用其中的信息，展示用户名和头像。</p>
    <pre>{
  "aud": "4", // 你的应用 ID
  "jti": "6c1ce28e816a124da6992f575fb4abefd7dce62ca967696aa7160e901d155c6f7cef4f56011a9889",
  "iss": "{{url('/')}}",
  "iat": 1720703823.752481,
  "exp": 1720707423.752481,
  "sub": "1",
  "id": 1,
  "avatar": "https://cravatar.cn/avatar/820794b2ee799e6bd2396fcd712a5ea8",
  "name": "伦",
  "email_verified": false,
  "real_name_verified": false,
  "phone_verified": false,
  "email": null,
  "phone": null,
  "created_at": "2024-06-18T17:30:15.000000Z"
}
    </pre>
@endsection
