@extends('layouts.app')

@section('title', '会员包')

@section('content')
    <h3>会员包</h3>

    <div class="mt-3">
        <a href="{{route('packages.list')}}" class="btn btn-primary btn-sm">购买会员包</a>
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>方案</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($user_packages as $up)
            <tr>
                <td>{{ $up->package->title }}</td>
                <td>
                    @if ($up->status == 'active')
                        激活
                    @elseif ($up->status == 'expired')
                        已过期
                    @endif
                    ，到期时间：{{ $up->expired_at }}

                </td>
                <td>
                    <a href="{{route('packages.renew', $up->package)}}" class="btn btn-primary btn-sm">续费</a>
                </td>

            </tr>
        @endforeach


        </tbody>
    </table>

@endsection
