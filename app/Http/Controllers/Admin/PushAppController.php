<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PushAppController extends Controller
{
    /**
     * 显示应用列表
     */
    public function index()
    {
        $apps = PushApp::paginate(10);
        return view('admin.push_apps.index', compact('apps'));
    }

    /**
     * 显示创建应用表单
     */
    public function create()
    {
        return view('admin.push_apps.create');
    }

    /**
     * 存储新创建的应用
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:255',
            'max_connections' => 'required|integer',
            'enable_client_messages' => 'required|boolean',
            'enabled' => 'required|boolean',
            'max_backend_events_per_sec' => 'required|integer',
            'max_client_events_per_sec' => 'required|integer',
            'max_read_req_per_sec' => 'required|integer',
            'max_presence_members_per_channel' => 'nullable|integer',
            'max_presence_member_size_in_kb' => 'nullable|integer',
            'max_channel_name_length' => 'nullable|integer',
            'max_event_channels_at_once' => 'nullable|integer',
            'max_event_name_length' => 'nullable|integer',
            'max_event_payload_in_kb' => 'nullable|integer',
            'max_event_batch_size' => 'nullable|integer',
            'webhooks' => 'nullable|json',
            'enable_user_authentication' => 'boolean',
        ]);

        // 生成唯一标识符和密钥
        $validated['id'] = $request->input('id');
        $validated['key'] = Str::random(32);
        $validated['secret'] = Str::random(64);

        // 转换布尔值为整数
        $validated['enable_client_messages'] = (int)$validated['enable_client_messages'];
        $validated['enabled'] = (int)$validated['enabled'];

        // 处理 enable_user_authentication，如果未提交则默认为 false
        $validated['enable_user_authentication'] = (int)($request->has('enable_user_authentication') ? $request->input('enable_user_authentication') : false);

        PushApp::create($validated);
        return redirect()->route('admin.push_apps.index')->with('success', '应用创建成功！');
    }

    /**
     * 显示指定的应用
     */
    public function show(PushApp $app)
    {
        return view('admin.push_apps.show', compact('app'));
    }

    /**
     * 显示编辑应用表单
     */
    public function edit(PushApp $app)
    {
        return view('admin.push_apps.edit', compact('app'));
    }

    /**
     * 更新指定的应用
     */
    public function update(Request $request, PushApp $app)
    {
        $validated = $request->validate([
            'max_connections' => 'sometimes|integer',
            'enable_client_messages' => 'sometimes|boolean',
            'enabled' => 'sometimes|boolean',
            'max_backend_events_per_sec' => 'sometimes|integer',
            'max_client_events_per_sec' => 'sometimes|integer',
            'max_read_req_per_sec' => 'sometimes|integer',
            'max_presence_members_per_channel' => 'nullable|integer',
            'max_presence_member_size_in_kb' => 'nullable|integer',
            'max_channel_name_length' => 'nullable|integer',
            'max_event_channels_at_once' => 'nullable|integer',
            'max_event_name_length' => 'nullable|integer',
            'max_event_payload_in_kb' => 'nullable|integer',
            'max_event_batch_size' => 'nullable|integer',
            'webhooks' => 'nullable|json',
            'enable_user_authentication' => 'sometimes|boolean',
        ]);

        // 转换布尔值为整数
        if (isset($validated['enable_client_messages'])) {
            $validated['enable_client_messages'] = (int)$validated['enable_client_messages'];
        }
        if (isset($validated['enabled'])) {
            $validated['enabled'] = (int)$validated['enabled'];
        }

        // 处理 enable_user_authentication，如果未提交则设置为 false
        $validated['enable_user_authentication'] = (int)($request->has('enable_user_authentication') ? $request->input('enable_user_authentication') : false);

        $app->update($validated);
        return redirect()->route('admin.push_apps.index')->with('success', '应用更新成功！');
    }

    /**
     * 删除指定的应用
     */
    public function destroy(PushApp $app)
    {
        $app->delete();
        return redirect()->route('admin.push_apps.index')->with('success', '应用删除成功！');
    }
}
