@extends('layouts.admin')

@section('title', "功能")

@section('content')
    <h2>功能</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.features.create')}}">新建</a>
        @if (Request::has('trashed'))
            <a href="{{route('admin.features.index')}}">显示全部</a>
            <p class="text-danger">你正在查看已删除的内容</p>

        @else
            <a href="{{route('admin.features.index')}}?trashed=true">显示已删除</a>
        @endif
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>名称</th>
            <th>设定</th>
            <th>周期</th>
            <th>额度</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($features as $feature)
            <tr>
                <td>
                    {{ $feature->name }}
                </td>

                <td>
                    启用配额: {{ $feature->consumable ? '是' : '否' }}
                    <br/>
                    超额付费: {{ $feature->postpaid ? '是' : '否' }}
                </td>

                <td>
                    {{ $feature->periodicity }}
                    {{ $feature->periodicity_type }}
                </td>
                <td>
                    {{ $feature->quota }}
                    @if ($feature->postpaid)
                        <small class="text-success">超额付费</small>
                    @endif
                    @if (!$feature->consumable)
                        <br/>
                        <small class="text-danger">设置了额度，但未启用配额</small>
                    @endif

                </td>

                <td>
                    @if($feature->trashed())
                        <form action="{{ route('admin.features.restore', $feature->id) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">恢复</button>
                        </form>

                    @else
                        <a href="{{ route('admin.features.edit', $feature->id) }}"
                           class="btn btn-sm btn-primary">编辑</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $features->links() }}

@endsection
