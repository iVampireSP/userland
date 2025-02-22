@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">为“{{ $client->name }}”配置推送</h5>
        </div>


        <div class="card-body">
            <x-alert-info>
                推送应用的配置将影响到所有使用该推送应用的客户端。
            </x-alert-info>
            <x-alert-info>
                此功能在实验阶段，部分配置无法更改。
            </x-alert-info>

            @if (session('secret'))
                <x-alert-success>
                    应用 Secret 为：{{ session('secret') }}
                </x-alert-success>
            @endif

            <form action="{{ route('clients.push-apps.update', $client->id) }}" method="POST">
                @csrf
                @method('PATCH')

                @if ($pushApp)
                    <div class="row">
                        <!-- 基本设置 -->
                        <div class="col-md-6 mb-3">
                            <h6 class="mb-3">基本设置</h6>


                            <div class="mb-3">
                                <label for="id" class="form-label">应用 ID</label>
                                <input type="text" class="form-control" id="id" name="id" value="{{ old('id', $pushApp->id) }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="key" class="form-label">应用 Key</label>
                                <input type="text" class="form-control" id="key" name="key"
                                    value="{{ old('key', $pushApp->key) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="max_connections" class="form-label">最大连接数</label>
                                <input type="number" class="form-control" id="max_connections" name="max_connections" readonly
                                    value="{{ old('max_connections', $pushApp->max_connections) }}" required>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" {{ old('enabled', $pushApp->enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enabled">启用应用</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_client_messages"
                                        name="enable_client_messages" value="1" {{ old('enable_client_messages', $pushApp->enable_client_messages) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_client_messages">允许客户端消息</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_user_authentication"
                                        name="enable_user_authentication" value="1" {{ old('enable_user_authentication', $pushApp->enable_user_authentication) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_user_authentication">启用用户认证</label>
                                </div>
                            </div>
                        </div>

                        <!-- 速率限制 -->
                        <div class="col-md-6 mb-3">
                            <h6 class="mb-3">速率限制</h6>

                            <div class="mb-3">
                                <label for="max_backend_events_per_sec" class="form-label">后端每秒最大事件数</label>
                                <input type="number" class="form-control" id="max_backend_events_per_sec" readonly
                                    name="max_backend_events_per_sec" readonly
                                    value="{{ old('max_backend_events_per_sec', $pushApp->max_backend_events_per_sec) }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="max_client_events_per_sec" class="form-label">客户端每秒最大事件数</label>
                                <input type="number" class="form-control" id="max_client_events_per_sec"
                                    name="max_client_events_per_sec" readonly
                                    value="{{ old('max_client_events_per_sec', $pushApp->max_client_events_per_sec) }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="max_read_req_per_sec" class="form-label">每秒最大读取请求数</label>
                                <input type="number" class="form-control" id="max_read_req_per_sec" name="max_read_req_per_sec"
                                    value="{{ old('max_read_req_per_sec', $pushApp->max_read_req_per_sec) }}" readonly required>
                            </div>
                        </div>

                        <!-- Presence 设置 -->
                        <div class="col-md-6 mb-3">
                            <h6 class="mb-3">Presence 设置</h6>

                            <div class="mb-3">
                                <label for="max_presence_members_per_channel" class="form-label">每个频道最大 Presence 成员数</label>
                                <input type="number" class="form-control" id="max_presence_memb
                                                                                ers_per_channel"
                                    name="max_presence_members_per_channel" readonly
                                    value="{{ old('max_presence_members_per_channel', $pushApp->max_presence_members_per_channel) }}">
                            </div>

                            <div class="mb-3">
                                <label for="max_presence_member_size_in_kb" class="form-label">Presence 成员大小限制 (KB)</label>
                                <input type="number" class="form-control" id="max_presence_member_size_in_kb"
                                    name="max_presence_member_size_in_kb" readonly
                                    value="{{ old('max_presence_member_size_in_kb', $pushApp->max_presence_member_size_in_kb) }}">
                            </div>
                        </div>

                        <!-- 事件设置 -->
                        <div class="col-md-6 mb-3">
                            <h6 class="mb-3">事件设置</h6>

                            <div class="mb-3">
                                <label for="max_channel_name_length" class="form-label">频道名称最大长度</label>
                                <input type="number" class="form-control" id="max_channel_name_length"
                                    name="max_channel_name_length" readonly
                                    value="{{ old('max_channel_name_length', $pushApp->max_channel_name_length) }}">
                            </div>

                            <div class="mb-3">
                                <label for="max_event_channels_at_once" class="form-label">单次最大事件频道数</label>
                                <input type="number" class="form-control" id="max_event_channels_at_once"
                                    name="max_event_channels_at_once" readonly
                                    value="{{ old('max_event_channels_at_once', $pushApp->max_event_channels_at_once) }}">
                            </div>

                            <div class="mb-3">
                                <label for="max_event_name_length" class="form-label">事件名称最大长度</label>
                                <input type="number" class="form-control" id="max_event_name_length"
                                    name="max_event_name_length" readonly
                                    value="{{ old('max_event_name_length', $pushApp->max_event_name_length) }}">
                            </div>

                            <div class="mb-3">
                                <label for="max_event_payload_in_kb" class="form-label">事件负载最大大小 (KB)</label>
                                <input type="number" class="form-control" id="max_event_payload_in_kb" readonly
                                    name="max_event_payload_in_kb"
                                    value="{{ old('max_event_payload_in_kb', $pushApp->max_event_payload_in_kb) }}">
                            </div>

                            <div class="mb-3">
                                <label for="max_event_batch_size" class="form-label">事件批处理最大大小</label>
                                <input type="number" class="form-control" id="max_event_batch_size" name="max_event_batch_size"
                                    readonly value="{{ old('max_event_batch_size', $pushApp->max_event_batch_size) }}">
                            </div>
                        </div>

                        <!-- Webhooks -->
                        <div class="col-12 mb-3">
                            <h6 class="mb-3">Webhooks</h6>

                            <div class="mb-3">
                                <label for="webhooks" class="form-label">Webhooks 配置 (JSON)</label>
                                <textarea class="form-control" id="webhooks" name="webhooks" readonly
                                    rows="3">{{ old('webhooks', $pushApp->webhooks) }}</textarea>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        没有创建推送应用，在你点击保存时，将创建一个推送应用。
                    </div>
                @endif

                <div class="text-end">
                    <a href="{{ route('clients.show', $client) }}" class="btn btn-secondary">取消</a>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>

            @if ($pushApp)
                <hr />

                <form action="{{ route('clients.push-apps.delete', $client->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">重置推送应用</button>
                </form>
            @endif

            <hr />

           {{-- 推送服务器列表 --}}
           <h6 class="mb-3">推送服务器列表</h6>

           <div class="mb-3">
                @php($push_servers = config('push.servers'))
                @if (count($push_servers))
                    <ul>
                        @foreach ($push_servers as $server)
                            <li>{{ $server }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>没有推送服务器</p>
                @endif
            </div>

        </div>
    </div>
@endsection
