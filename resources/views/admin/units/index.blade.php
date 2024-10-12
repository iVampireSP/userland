@extends('layouts.admin')

@section('title', "计价单位")

@section('content')
    <h2>计价单位</h2>
    <p>计价单位可以很轻松的将账单转化为实际消费</p>

    <div class="mb-3">
        <a href="{{route('admin.unit_prices.create')}}">新建</a>
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>单位</th>
            <th>描述</th>
            <th>每单位价格</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($unit_prices as $unit_price)
            <tr>
                <td>
                    {{ $unit_price->unit }}
                </td>
                <td>
                    {{ $unit_price->name }}
                </td>
                <td>
                    {{ $unit_price->price_per_unit }}
                </td>
                <td>
                    <a href="{{ route('admin.unit_prices.edit', $unit_price) }}">编辑</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $unit_prices->links() }}

@endsection
