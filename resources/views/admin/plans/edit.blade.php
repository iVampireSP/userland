@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>编辑方案</h2>
                <a href="{{route('admin.plans.index')}}">返回</a>

                <form action="{{ route('admin.plans.update', $plan) }}" method="post" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-floating mb-3">
                        <input type="text" id="name" name="name" class="form-control" value="{{ $plan->name }}"
                               placeholder="方案名称">
                        <label for="name">方案</label>
                    </div>

                    {{--                    计费周期（日，周，月，年--}}
                    <div class="form-floating mb-3">
                        <select class="form-select" aria-label="计费周期" name="periodicity_type">
                            <option
                                    @selected(old('periodicity_type', $plan->periodicity_type) == 'year') value="year">
                                年
                            </option>
                            <option
                                    @selected(old('periodicity_type', $plan->periodicity_type) == 'month') value="month">
                                月
                            </option>
                            <option
                                    @selected(old('periodicity_type', $plan->periodicity_type) == 'week')  value="week">
                                周
                            </option>
                            <option
                                    @selected(old('periodicity_type', $plan->periodicity_type) == 'day')  value="day">
                                日
                            </option>
                            <option
                                    @selected(old('periodicity_type', $plan->periodicity_type) == 'none')  value="none">
                                无
                            </option>

                        </select>
                        <label for="periodicity_type">计费周期</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" id="periodicity" name="periodicity" class="form-control"
                               placeholder="周期" value="{{ $plan->periodicity }}">
                        <label for="periodicity">周期值</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" id="grace_days" name="grace_days" min="0" class="form-control"
                               placeholder="周期" value="{{ $plan->grace_days }}">
                        <label for="grace_days">宽限天数（方案到期后还能继续使用多少天）</label>
                    </div>


                    <button type="submit" class="btn btn-primary mt-3">编辑</button>

                </form>

                <hr />

                <form action="{{route('admin.plans.destroy', $plan)}}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3">删除</button>
                </form>
            </div>
        </div>

@endsection
