@extends('layouts.app')

@section('content')
    <h2>封禁列表</h2>
    <p>只有受信任的应用程序才能向您施加封禁。</p>


    <div class="overflow-auto mt-3">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>代码</th>
            <th>应用</th>
            <th>封禁原因</th>
            <th>封禁时间</th>
            <th>解封时间</th>
            <th>状态</th>
            </thead>

            <tbody>
            @foreach ($bans as $ban)
                <tr>
                    <td>
                        {{ $ban->id }}
                    </td>
                    <td>
                        {{ $ban->code }}
                    </td>
                    <td>
                        {{ $ban->client->name }}
                    </td>
                    <td>
                        {{ $ban->reason }}
                    </td>
                    <td>
                        {{ $ban->created_at }}
                    </td>
                    <td>
                        {{$ban->expired_at ?? '永久'}}
                    </td>
                    <td>
                        @if ($ban->pardoned)
                            <i class="bi bi-check-circle text-success"></i> 已解除
                        @else
                            <i class="bi bi-x-circle text-danger"></i> 封禁中
                        @endif
                    </td>
                </tr>

            @endforeach
            </tbody>
        </table>
    </div>

    <p>我们会每小时检查一次封禁状态，如果您的封禁到期但是未被解除，请等候一小时后重试。<br />如果您不了解对应的封禁，请联系应用程序管理员。</p>

    {{ $bans->links() }}

@endsection
