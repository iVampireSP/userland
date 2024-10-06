@extends('layouts.admin')

@section('title', "配额")

@section('content')
    <h2>配额</h2>
    <p></p>

    <div class="mb-3">
        <a href="{{route('admin.packages.edit', $package)}}">返回</a>
        <a href="{{route('admin.quotas.index')}}">导航至配额</a>
    </div>

    <h3>已绑定的计量单位</h3>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>描述</th>
            <th>单位</th>
            <th>最大值</th>
            <th>重置规则</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($package->quotas as $quota)
            <tr>
                <td>
                    {{ $quota->quota->description }}
                </td>
                <td>
                    {{ $quota->quota->unit }}
                </td>
                <td>
                    @if ($quota->max_amount == 0)
                        不限制
                    @else
                        {{ $quota->max_amount }}
                    @endif
                </td>
                <td>
                   <span>
                       @switch($quota->reset_rule)
                           @case('none')
                               不重置
                               @break
                           @case('day')
                               每天
                               @break
                           @case('week')
                               每周
                               @break
                           @case('month')
                               每月
                               @break
                           @case('half_year')
                               每半年
                               @break
                           @case('year')
                               每年
                               @break

                       @endswitch
                        </span>
                </td>
                <td>
                    <form action="{{ route('admin.packages.quotas.destroy', [$package, $quota->quota]) }}"
                          method="post">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-sm btn-danger">取消绑定</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>


    <h3>计量单位列表</h3>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>描述</th>
            <th>单位</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($quotas as $quota)
            <tr>
                <td>
                    {{ $quota->description }}
                </td>
                <td>
                    {{ $quota->unit }}
                </td>
                <td>
                    <form action="{{ route('admin.packages.quotas.update', [$package, $quota]) }}"
                          method="post">
                        @csrf
                        @method('PATCH')
                        <select class="form-select form-select-sm" aria-label="重置规则" name="reset_rule">
                            <option @selected($quota->reset_rule == 'none') value="none">不重置</option>
                            <option @selected($quota->reset_rule == 'day') value="day">每日重置</option>
                            <option @selected($quota->reset_rule == 'week') value="week">每周重置</option>
                            <option @selected($quota->reset_rule == 'month') value="month">每月重置</option>
                            <option @selected($quota->reset_rule == 'half_year') value="half_year">每半年重置</option>
                            <option @selected($quota->reset_rule == 'year') value="year">每年重置</option>
                        </select>

                        <input type="text" name="max_amount" class="form-control form-control-sm" placeholder="最大值（0 为不限制）">
                        <button type="submit" class="btn btn-sm btn-primary">绑定</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $quotas->links() }}

@endsection
