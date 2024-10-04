@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h3>绑定的功能</h3>

                <div class="mt-3">
                    <a href="{{ route('admin.plans.edit', $plan->id) }}">返回编辑</a>
                </div>

                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($features as $feature)
                        <tr>
                            <td>{{ $feature->id }}</td>
                            <td>{{ $feature->name }}</td>
                            <td>
                                <form action="{{ route('admin.plans.toggleFeature', [$plan, $feature]) }}"
                                      method="post">
                                    @csrf
                                    @if ($plan->features->contains($feature))
                                        <button type="submit" class="btn btn-sm btn-danger">取消绑定</button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-primary">绑定</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
