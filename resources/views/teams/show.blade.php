@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- 顶部导航和操作区 -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('teams.index') }}">团队</a></li>
                    <li class="breadcrumb-item active">{{ $team->name }}</li>
                </ol>
            </nav>
            <h2 class="mb-0 fw-bold">{{ $team->name }}</h2>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            @if($team->owner_id === auth()->id())
                <a href="{{ route('teams.edit', $team) }}" class="btn btn-outline-primary d-flex align-items-center">
                    <i class="bi bi-pencil me-2"></i> 编辑团队
                </a>
                <form action="{{ route('teams.destroy', $team) }}" method="POST" onsubmit="return confirm('确定要删除此团队吗？所有成员将被移除。');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger d-flex align-items-center">
                        <i class="bi bi-trash me-2"></i> 删除团队
                    </button>
                </form>
            @elseif(auth()->user()->can('leaveTeam', $team))
                <form action="{{ route('teams.leave', $team) }}" method="POST" onsubmit="return confirm('确定要离开此团队吗？');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger d-flex align-items-center">
                        <i class="bi bi-box-arrow-right me-2"></i> 离开团队
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- 左侧团队信息 -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">团队信息</h5>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">创建者</div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle me-2"></i>
                            <span>{{ $members->where('id', $team->owner_id)->first()->name ?? '未知' }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">创建时间</div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar3 me-2"></i>
                            <span>{{ $team->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">成员数量</div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-people me-2"></i>
                            <span>{{ $members->count() }} 人</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-muted small mb-1">团队描述</div>
                        <p class="mb-0">{{ $team->description ?: '暂无描述' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧成员管理 -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold mb-0">团队成员</h5>
                        @can('invite', $team)
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteModal">
                            <i class="bi bi-person-plus me-1"></i> 邀请成员
                        </button>
                        @endcan
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>成员</th>
                                    <th>角色</th>
                                    <th>加入时间</th>
                                    @if($team->owner_id === auth()->id())
                                    <th>操作</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $member->name }}</div>
                                                <div class="small text-muted">{{ $member->email }}</div>
                                            </div>
                                            @if($member->id === $team->owner_id)
                                            <span class="badge bg-warning ms-2">创建者</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($team->owner_id === auth()->id() && $member->id !== auth()->id())
                                        <form action="{{ route('teams.members.role', [$team, $member]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="member" {{ $member->pivot->role === 'member' ? 'selected' : '' }}>成员</option>
                                                <option value="admin" {{ $member->pivot->role === 'admin' ? 'selected' : '' }}>管理员</option>
                                            </select>
                                        </form>
                                        @else
                                        <span class="badge {{ $member->pivot->role === 'admin' ? 'bg-info' : 'bg-secondary' }}">{{ $member->pivot->role }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $member->pivot->created_at->format('Y-m-d') }}</span>
                                    </td>
                                    @if($team->owner_id === auth()->id())
                                    <td>
                                        @if($member->id !== auth()->id())
                                        <form action="{{ route('teams.members.remove', [$team, $member]) }}" method="POST" class="d-inline" onsubmit="return confirm('确定要移除此成员吗？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-person-x"></i> 移除
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 邀请成员模态框 -->
@can('invite', $team)
<div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteModalLabel">邀请新成员</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teams.invite', $team) }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">成员邮箱</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="请输入要邀请的成员邮箱" required>
                        <div class="form-text">该邮箱必须已在系统中注册</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">发送邀请</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

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
