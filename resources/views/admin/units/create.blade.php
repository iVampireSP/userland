@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>创建计量单位</h2>
                <a href="{{route('admin.unit_prices.index')}}">返回</a>

                <form action="{{ route('admin.unit_prices.store') }}" method="post" class="mt-3">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" id="unit" name="unit" class="form-control" value="{{ old('unit') }}"
                               placeholder="标识">
                        <label for="unit">标识（比如 tokens）</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                               placeholder="名称">
                        <label for="description">名称</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" id="price_per_unit" name="price_per_unit" class="form-control" value="{{ old('price_per_unit') }}"
                               placeholder="每单位价格">
                        <label for="price_per_unit">每单位价格</label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">新建</button>
                </form>

            </div>
        </div>
    </div>
@endsection
