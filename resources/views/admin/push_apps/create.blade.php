@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">创建新应用</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.push_apps.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- 基本设置 -->
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-3">基本设置</h6>


                        <div class="mb-3">
                            <label for="id" class="form-label">应用 ID</label>
                            <input type="text" class="form-control" id="id" name="id"
                                value="{{ old('id') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="max_connections" class="form-label">最大连接数</label>
                            <input type="number" class="form-control" id="max_connections" name="max_connections"
                                value="{{ old('max_connections', 1000) }}" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" {{ old('enabled', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enabled">启用应用</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_client_messages"
                                    name="enable_client_messages" value="1" {{ old('enable_client_messages', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_client_messages">允许客户端消息</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_user_authentication"
                                    name="enable_user_authentication" value="1" {{ old('enable_user_authentication', false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_user_authentication">启用用户认证</label>
                            </div>
                        </div>
                    </div>

                    <!-- 速率限制 -->
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-3">速率限制</h6>

                        <div class="mb-3">
                            <label for="max_backend_events_per_sec" class="form-label">后端每秒最大事件数</label>
                            <input type="number" class="form-control" id="max_backend_events_per_sec"
                                name="max_backend_events_per_sec" value="{{ old('max_backend_events_per_sec', 100) }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="max_client_events_per_sec" class="form-label">客户端每秒最大事件数</label>
                            <input type="number" class="form-control" id="max_client_events_per_sec"
                                name="max_client_events_per_sec" value="{{ old('max_client_events_per_sec', 100) }}"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="max_read_req_per_sec" class="form-label">每秒最大读取请求数</label>
                            <input type="number" class="form-control" id="max_read_req_per_sec" name="max_read_req_per_sec"
                                value="{{ old('max_read_req_per_sec', 100) }}" required>
                        </div>
                    </div>

                    <!-- Presence 设置 -->
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-3">Presence 设置</h6>

                        <div class="mb-3">
                            <label for="max_presence_members_per_channel" class="form-label">每个频道最大 Presence 成员数</label>
                            <input type="number" class="form-control" id="max_presence_members_per_channel"
                                name="max_presence_members_per_channel"
                                value="{{ old('max_presence_members_per_channel') }}">
                        </div>

                        <div class="mb-3">
                            <label for="max_presence_member_size_in_kb" class="form-label">Presence 成员大小限制 (KB)</label>
                            <input type="number" class="form-control" id="max_presence_member_size_in_kb"
                                name="max_presence_member_size_in_kb" value="{{ old('max_presence_member_size_in_kb') }}">
                        </div>
                    </div>

                    <!-- 事件设置 -->
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-3">事件设置</h6>

                        <div class="mb-3">
                            <label for="max_channel_name_length" class="form-label">频道名称最大长度</label>
                            <input type="number" class="form-control" id="max_channel_name_length"
                                name="max_channel_name_length" value="{{ old('max_channel_name_length') }}">
                        </div>

                        <div class="mb-3">
                            <label for="max_event_channels_at_once" class="form-label">单次最大事件频道数</label>
                            <input type="number" class="form-control" id="max_event_channels_at_once"
                                name="max_event_channels_at_once" value="{{ old('max_event_channels_at_once') }}">
                        </div>

                        <div class="mb-3">
                            <label for="max_event_name_length" class="form-label">事件名称最大长度</label>
                            <input type="number" class="form-control" id="max_event_name_length"
                                name="max_event_name_length" value="{{ old('max_event_name_length') }}">
                        </div>

                        <div class="mb-3">
                            <label for="max_event_payload_in_kb" class="form-label">事件负载最大大小 (KB)</label>
                            <input type="number" class="form-control" id="max_event_payload_in_kb"
                                name="max_event_payload_in_kb" value="{{ old('max_event_payload_in_kb') }}">
                        </div>

                        <div class="mb-3">
                            <label for="max_event_batch_size" class="form-label">事件批处理最大大小</label>
                            <input type="number" class="form-control" id="max_event_batch_size" name="max_event_batch_size"
                                value="{{ old('max_event_batch_size') }}">
                        </div>
                    </div>

                    <!-- Webhooks -->
                    <div class="col-12 mb-3">
                        <h6 class="mb-3">Webhooks</h6>

                        <div class="mb-3">
                            <label for="webhooks" class="form-label">Webhooks 配置 (JSON)</label>
                            <textarea class="form-control" id="webhooks" name="webhooks"
                                rows="3">{{ old('webhooks') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('admin.push_apps.index') }}" class="btn btn-secondary">取消</a>
                    <button type="submit" class="btn btn-primary">创建应用</button>
                </div>
            </form>
        </div>
    </div>
@endsection
