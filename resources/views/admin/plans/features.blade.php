@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h3>管理功能</h3>

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
                                        @if ($feature->consumable)
                                            <input type="number" name="charges" class="form-control"
                                                   placeholder="额度增量" min="1"
                                                   value="{{ old('charges') }}">
                                        @endif

                                        <button type="submit" class="btn btn-sm btn-primary">绑定</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div>
                    <small>
                        额度增量：比如一个低级的订阅切换到更高级的订阅，所对应的功能的额度将会得到提升。
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection
