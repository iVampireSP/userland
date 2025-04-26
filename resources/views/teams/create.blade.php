@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- 顶部导航 -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teams.index') }}">团队</a></li>
                <li class="breadcrumb-item active">创建团队</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0">创建新团队</h2>
        <p class="text-muted">创建一个新的团队并邀请成员加入</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('teams.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label fw-medium">团队名称 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-people"></i>
                                </span>
                                <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                    name="name" value="{{ old('name') }}" placeholder="输入团队名称" required autofocus>
                            </div>
                            @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">请使用简洁易记的名称，最多255个字符</div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-medium">团队描述</label>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                                name="description" rows="4" placeholder="请描述这个团队的目标和用途">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">可选填写，最多1000个字符</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-plus-circle me-2"></i> 创建团队
                            </button>
                            <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-arrow-left me-2"></i> 返回
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 右侧提示信息 -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 bg-light">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold">关于团队</h5>
                    <p class="card-text">团队可以帮助您与同事或合作伙伴一起协作。创建团队后，您将成为团队的所有者。</p>

                    <h6 class="fw-bold mt-3">团队创建后您可以：</h6>
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item bg-transparent px-0">
                            <i class="bi bi-check-circle-fill text-success me-2"></i> 邀请新成员加入团队
                        </li>
                        <li class="list-group-item bg-transparent px-0">
                            <i class="bi bi-check-circle-fill text-success me-2"></i> 管理团队成员权限
                        </li>
                        <li class="list-group-item bg-transparent px-0">
                            <i class="bi bi-check-circle-fill text-success me-2"></i> 随时编辑团队信息
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 自动关闭提示消息
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function() {
            $(this).remove();
        });
    }, 3000);
</script>
@endsection
