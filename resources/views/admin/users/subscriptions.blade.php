@extends('layouts.admin')

@section('title', "订阅计划")

@section('content')
    <h2>计划</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.plans.create')}}">新建</a>
        @if (Request::has('trashed'))
            <a href="{{route('admin.plans.index')}}">显示全部</a>
            <p class="text-danger">你正在查看已删除的内容</p>

        @else
            <a href="{{route('admin.plans.index')}}?trashed=true">显示已删除</a>
        @endif
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>名称</th>
            <th>周期</th>
            <th>宽恕天数</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($plans as $plan)
            <tr>
                <td>
                    {{ $plan->name }}
                </td>


                <td>
                    {{ $plan->periodicity }}
                    {{ $plan->periodicity_type }}
                </td>
                <td>
                    {{ $plan->grace_days }} 天
                </td>

                <td>
                    @if($plan->trashed())
                        <form action="{{ route('admin.plans.restore', $plan->id) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">恢复</button>
                        </form>

                    @else
                        <a href="{{ route('admin.plans.edit', $plan->id) }}"
                           class="btn btn-sm btn-primary">编辑</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $plans->links() }}

@endsection
