@extends('layouts.app')

@section('title', '会员包')

@section('content')
    <h3>会员包</h3>
    <p>购买一个会员包。</p>
    <p>我们正在测试阶段，不会收取您实际的费用。</p>

    @foreach($categories as $cg)
        <h4>{{$cg->name}}</h4>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>方案</th>
                <th>描述</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($cg->packages as $package)
                <tr>
                    <td>{{ $package->title }}</td>
                    <td>
                        {{ $package->description }}
                    </td>
                    <td>
                        {{--                    下单--}}
                        <a href="{{route('packages.show', $package)}}" class="btn btn-primary btn-sm">购买</a>
                    </td>
                </tr>
            @endforeach


            </tbody>
        </table>
    @endforeach


@endsection
