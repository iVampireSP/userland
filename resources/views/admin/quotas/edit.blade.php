@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>编辑 {{ $quota->name }}</h2>
                <a href="{{route('admin.quotas.index')}}">返回</a>

                <form action="{{ route('admin.quotas.update', $quota) }}" method="post" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-floating mb-3">
                        <input type="text" id="unit" name="unit" class="form-control" value="{{ $quota->unit }}"
                               placeholder="标识">
                        <label for="unit">标识（比如 tokens）</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" id="description" name="description" class="form-control" value="{{ $quota->description  }}"
                               placeholder="描述">
                        <label for="description">描述</label>
                    </div>


                    <button type="submit" class="btn btn-primary mt-3">编辑</button>
                </form>


                <hr/>

                <form action="{{route('admin.quotas.destroy', $quota)}}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3">删除</button>
                </form>
            </div>
        </div>

@endsection
