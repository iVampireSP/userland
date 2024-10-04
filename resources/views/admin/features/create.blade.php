@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>功能</h2>
                <a href="{{route('admin.features.index')}}">返回</a>

                <form action="{{ route('admin.features.store') }}" method="post" class="mt-3">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                               placeholder="特性名称">
                        <label for="name">标识名（比如 custom-domain）</label>
                    </div>

                    {{--                    计费周期（日，周，月，年--}}
                    <div class="form-floating mb-3">
                        <select class="form-select" aria-label="计费周期" name="periodicity_type">
                            <option
                                @selected(old('periodicity_type') == 'year') value="year">年
                            </option>
                            <option
                                @selected(old('periodicity_type') == 'month') value="month">
                                月
                            </option>
                            <option
                                @selected(old('periodicity_type') == 'week')  value="week">周
                            </option>
                            <option
                                @selected(old('periodicity_type') == 'day')  value="day">日
                            </option>
                            <option
                                @selected(old('periodicity_type') == 'none')  value="none">无
                            </option>

                        </select>
                        <label for="periodicity_type">计费周期</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" id="periodicity" name="periodicity" class="form-control"
                               placeholder="周期" value="{{ old('periodicity') }}">
                        <label for="periodicity">周期值</label>
                    </div>


                    <div class="form-check">
                        <input class="form-check-input" @checked(old('consumable', false)) type="checkbox" value="1"
                               name="consumable"
                               id="consumable">
                        <label class="form-check-label" for="consumable">
                            启用配额
                        </label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" id="quota" name="quota" value="{{old('quota')}}" class="form-control"
                               placeholder="额度">
                        <label for="quota">额度（需要配额）</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="postpaid"
                               @checked(old('postpaid', false))
                               id="postpaid">
                        <label class="form-check-label" for="postpaid">
                            后付费（如果超过额度）
                        </label>
                    </div>


                    <button type="submit" class="btn btn-primary mt-3">新建</button>
                </form>
            </div>
        </div>

@endsection
