@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- 顶部区域 -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('teams.index') }}">团队</a></li>
                    <li class="breadcrumb-item active">团队邀请</li>
                </ol>
            </nav>
            <h2 class="mb-0 fw-bold">团队邀请</h2>
            <p class="text-muted mb-0">管理您收到的所有团队邀请</p>
        </div>
    </div>

    <!-- 邀请列表 -->
    @if($invitations->isEmpty())
    <div class="text-center py-5 my-4 bg-light rounded-3">
        <div class="py-3">
            <i class="bi bi-envelope-check fs-1 text-muted mb-3"></i>
            <h4>暂无待处理的团队邀请</h4>
            <p class="text-muted">当有人邀请您加入团队时，邀请将显示在这里</p>
            <a href="{{ route('teams.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-people me-1"></i> 查看我的团队
            </a>
        </div>
    </div>
    @else
    <div class="row row-cols-1 row-cols-md-2 g-4">
        @foreach($invitations as $invitation)
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">{{ $invitation->team->name }}</h5>
                            <p class="text-muted mb-0 small">邀请时间: {{ $invitation->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person me-2 text-muted"></i>
                            <span>邀请人: {{ $invitation->team->owner->name }}</span>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="bi bi-envelope me-2 text-muted"></i>
                            <span>邀请人邮箱: {{ $invitation->team->owner->email }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <form action="{{ route('teams.invitations.accept', $invitation) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-2"></i> 接受邀请
                            </button>
                        </form>
                        <form action="{{ route('teams.invitations.reject', $invitation) }}" method="POST" class="flex-grow-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-x-circle me-2"></i> 拒绝邀请
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
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
