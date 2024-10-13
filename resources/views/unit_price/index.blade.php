@extends('layouts.app')

@section('title', '计价单位')

@section('content')
    <h3>计价单位</h3>


    <table class="table table-striped">
        <thead>
        <tr>
            <th>单位</th>
            <th>名称</th>
            <th>每单位价格</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($unitPrice as $up)
            <tr>
                <td>{{ $up->unit }}</td>
                <td>
                    {{ $up->name }}
                </td>
                <td>
                   {{ $up->price_per_unit }}
                </td>
            </tr>
        @endforeach


        </tbody>
    </table>

@endsection
