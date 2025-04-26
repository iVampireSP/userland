@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- 顶部区域 -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="mb-0 fw-bold">我的团队</h2>
            <p class="text-muted mb-0">管理您所属的所有团队</p>
        </div>
        <a href="{{ route('teams.create') }}" class="btn btn-primary d-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> 创建新团队
        </a>
    </div>

    <!-- 团队列表 -->
    @if($teams->isEmpty())
    <div class="text-center py-5 my-4 bg-light rounded-3">
        <div class="py-3">
            <i class="bi bi-people fs-1 text-muted mb-3"></i>
            <h4>您还没有加入任何团队</h4>
            <p class="text-muted">创建或加入一个团队，开始协作</p>
            <a href="{{ route('teams.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i> 创建团队
            </a>
        </div>
    </div>
    @else
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        @foreach($teams as $team)
        <div class="col">
            <div class="card h-100 border-0 shadow-sm {{ auth()->user()->current_team_id === $team->id ? 'border-primary border-3' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0 fw-bold">{{ $team->name }}</h5>
                        @if(auth()->user()->current_team_id === $team->id)
                        <span class="badge bg-primary">当前团队</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <span class="badge bg-light text-dark">{{ $team->pivot->role }}</span>
                        <span class="text-muted small ms-2">创建于 {{ $team->created_at->format('Y-m-d') }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="teamActions{{ $team->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                操作
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="teamActions{{ $team->id }}">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('teams.show', $team) }}">
                                        <i class="bi bi-eye me-2"></i> 查看详情
                                    </a>
                                </li>
                                @if($team->owner_id === auth()->id())
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('teams.edit', $team) }}">
                                        <i class="bi bi-pencil me-2"></i> 编辑团队
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('teams.destroy', $team) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center" onclick="return confirm('确定要删除此团队吗？所有成员将被移除。')">
                                            <i class="bi bi-trash me-2"></i> 删除团队
                                        </button>
                                    </form>
                                </li>
                                @else
                                <li>
                                    <form action="{{ route('teams.members.remove', [$team, auth()->user()]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center" onclick="return confirm('确定要离开此团队吗？')">
                                            <i class="bi bi-box-arrow-right me-2"></i> 离开团队
                                        </button>
                                    </form>
                                </li>
                                @endif
                            </ul>
                        </div>

                        @if(auth()->user()->current_team_id !== $team->id)
                        <form action="{{ route('teams.switch', $team) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-right-circle me-1"></i> 切换
                            </button>
                        </form>
                        @endif
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
