@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>创建计量单位</h2>
                <a href="{{route('admin.quotas.index')}}">返回</a>

                <form action="{{ route('admin.quotas.store') }}" method="post" class="mt-3">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" id="unit" name="unit" class="form-control" value="{{ old('unit') }}"
                               placeholder="标识">
                        <label for="unit">标识（比如 tokens）</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" id="description" name="description" class="form-control" value="{{ old('description') }}"
                               placeholder="描述">
                        <label for="description">描述</label>
                    </div>


                    <button type="submit" class="btn btn-primary mt-3">新建</button>
                </form>

            </div>
        </div>
    </div>
@endsection
