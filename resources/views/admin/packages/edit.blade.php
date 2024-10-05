@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>编辑 {{ $package->name }}</h2>
                <a href="{{route('admin.packages.index')}}">返回</a>
                <a href="{{route('admin.packages.quotas.index', $package)}}">配额</a>
                <a href="{{route('admin.packages.roles', $package)}}">角色</a>
                <a href="{{route('admin.packages.permissions', $package)}}">权限</a>

                <form action="{{ route('admin.packages.update', $package) }}" method="post" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">标题</label>
                                <input type="text" name="title" id="title" class="form-control"
                                       value="{{ $package->title }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">描述</label>
                                <input type="text" name="description" id="description" class="form-control"
                                       value="{{ $package->description }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">内容</label>
                                <textarea name="content" id="content" class="form-control" rows="4"
                                          required>{{ $package->content }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">标识名</label>
                                <input type="text" name="name" id="name" class="form-control"
                                       value="{{ $package->name }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">分类</label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    @foreach($categories as $category)
                                        <option
                                            value="{{ $category->id }}" @selected(old('category_id', $package->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>启用计费周期</h5>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="enable_day" id="enable_day" class="form-check-input"
                                       value="1" {{ $package->enable_day ? 'checked' : '' }}>
                                <label for="enable_day" class="form-check-label">按日付款</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="enable_week" id="enable_week" class="form-check-input"
                                       value="1" {{ $package->enable_week ? 'checked' : '' }}>
                                <label for="enable_week" class="form-check-label">按周付款</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="enable_month" id="enable_month" class="form-check-input"
                                       value="1" {{ $package->enable_month ? 'checked' : '' }}>
                                <label for="enable_month" class="form-check-label">按月付款</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="enable_year" id="enable_year" class="form-check-input"
                                       value="1" {{ $package->enable_year ? 'checked' : '' }}>
                                <label for="enable_year" class="form-check-label">按年付款</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="enable_forever" id="enable_forever"
                                       class="form-check-input"
                                       value="1" {{ $package->enable_forever ? 'checked' : '' }}>
                                <label for="enable_forever" class="form-check-label">永久有效</label>
                            </div>

                            <h5>价格设置</h5>
                            <div class="mb-3">
                                <label for="price_day" class="form-label">按日价格</label>
                                <input type="number" name="price_day" id="price_day" class="form-control" step="0.01"
                                       value="{{ $package->price_day }}" placeholder="可选">
                            </div>
                            <div class="mb-3">
                                <label for="price_week" class="form-label">按周价格</label>
                                <input type="number" name="price_week" id="price_week" class="form-control" step="0.01"
                                       value="{{ $package->price_week }}" placeholder="可选">
                            </div>
                            <div class="mb-3">
                                <label for="price_month" class="form-label">按月价格</label>
                                <input type="number" name="price_month" id="price_month" class="form-control"
                                       step="0.01" value="{{ $package->price_month }}" placeholder="可选">
                            </div>
                            <div class="mb-3">
                                <label for="price_year" class="form-label">按年价格</label>
                                <input type="number" name="price_year" id="price_year" class="form-control" step="0.01"
                                       value="{{ $package->price_year }}" placeholder="可选">
                            </div>
                            <div class="mb-3">
                                <label for="price_forever" class="form-label">永久价格</label>
                                <input type="number" name="price_forever" id="price_forever" class="form-control"
                                       step="0.01" value="{{ $package->price_forever }}" placeholder="可选">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">编辑</button>
                </form>


                <hr/>

                <form action="{{route('admin.packages.destroy', $package)}}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3">删除</button>
                </form>
            </div>
        </div>

@endsection
