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
                    {{ $quota->max_amount }}
                </td>
                <td>
                    <form action="{{ route('admin.packages.quotas.destroy', [$package, $quota]) }}"
                          method="post">
                        @csrf
                        @method('PATCH')

                        @if ($package->quotas->contains($quota))
                            <button type="submit" class="btn btn-sm btn-danger">取消绑定</button>
                        @else
                            <input type="text" name="max_amount" class="form-control form-control-sm"
                                   placeholder="最大值">
                            <button type="submit" class="btn btn-sm btn-primary">绑定</button>
                        @endif
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

                        <input type="text" name="max_amount" class="form-control form-control-sm" placeholder="最大值">
                        <button type="submit" class="btn btn-sm btn-primary">绑定</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $quotas->links() }}

@endsection
