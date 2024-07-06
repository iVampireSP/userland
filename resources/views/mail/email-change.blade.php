# 您正在更改邮件地址

您正在 <a href="{{ url('/') }}">{{ config('app.display_name') }}</a> 上更改邮件地址。

@if($user->email)
    您原来的邮箱地址是：{{ $user->email }}。
    新的邮件地址是：{{ $email }}。
@else
    新的邮箱地址是：{{ $email }}。
@endif

如果您确认要更改，请<a href="{{ $link }}">点击此链接</a>。

如果您无法正常点击，您可以复制此链接并到登录原账户的浏览器中打开。
{{ $link }}

如果您没有执行此操作，请忽略此邮件。
