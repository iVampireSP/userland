@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- 顶部导航 -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teams.index') }}">团队</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teams.show', $team) }}">{{ $team->name }}</a></li>
                <li class="breadcrumb-item active">编辑</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-0">编辑团队</h2>
        <p class="text-muted">更新团队信息和设置</p>
    </div>
    
    <!-- 错误提示 -->
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <h5 class="alert-heading">请检查以下错误：</h5>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- 提示消息 -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('teams.update', $team) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="name" class="form-label fw-medium">团队名称 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-people"></i>
                                </span>
                                <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                    name="name" value="{{ old('name', $team->name) }}" required>
                            </div>
                            @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-medium">团队描述</label>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror" 
                                name="description" rows="4" placeholder="请描述这个团队的目标和用途">{{ old('description', $team->description) }}</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-save me-2"></i> 保存更改
                            </button>
                            <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi bi-x-circle me-2"></i> 取消
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- 右侧团队信息 -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title fw-bold">团队信息</h5>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">创建者</div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle me-2"></i>
                            <span>{{ $team->owner->name ?? '未知' }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">创建时间</div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar3 me-2"></i>
                            <span>{{ $team->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 bg-danger bg-opacity-10 border-danger">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-danger">危险操作</h5>
                    <p class="card-text">删除团队将移除所有成员关联。此操作不可逆。</p>
                    <form action="{{ route('teams.destroy', $team) }}" method="POST" onsubmit="return confirm('确定要删除此团队吗？所有成员将被移除。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash me-2"></i> 删除团队
                        </button>
                    </form>
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
