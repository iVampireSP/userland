@extends('layouts.app')

@section('title', '订单')

@section('content')
    <h3>订单</h3>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>类型</th>
            <th>目标</th>
            <th>金额</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>
                    @if ($order->type == 'package')
                        套餐
                    @elseif ($order->type == 'package_upgrade')
                        升级
                    @elseif ($order->type == 'package_renewal')
                        续费
                    @elseif ($order->type == 'recharge')
                        余额充值
                    @endif
                </td>
                <td>
                    @if ($order->type == 'package')
                        {{ $order->package?->title }}
                    @elseif ($order->type == 'package_upgrade')
                        {{ $order->upgrade_to_package_id?->title }}
                    @elseif ($order->type == 'package_renewal')
                        {{ $order->package?->title }}
                    @elseif ($order->type == 'recharge')
                       账户
                    @endif
                </td>
                <td>
                    {{ $order->amount }} 元
                </td>

                <td>
                    @if ($order->status == 'unpaid')
                        <a href="{{route('orders.show', $order)}}" class="btn btn-primary btn-sm">付款</a>
                    @elseif ($order->status == "paid")
                        <span class="text-success">您已支付</span>
                    @elseif ($order->status == "cancelled")
                        已取消
                    @endif
                </td>
            </tr>
        @endforeach


        </tbody>
    </table>

    {{ $orders->links() }}

@endsection
