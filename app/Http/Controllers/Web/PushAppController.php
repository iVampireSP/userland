<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PushAppController extends Controller
{
    public function index(Client $client)
    {
        $this->authorize('managePush', $client);

        $pushApp = $client->pushApps()->first();

        return view('clients.push_apps.manage', compact('client', 'pushApp'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('managePush', $client);
        // 验证请求数据，将 checkbox 未选中时的值设为 false
        $validated = $request->validate([
            'enabled' => 'boolean',
            'enable_client_messages' => 'boolean',
            'enable_user_authentication' => 'boolean',
        ]);

        // 设置未选中 checkbox 的默认值为 false
        $validated['enabled'] = $request->boolean('enabled', false);
        $validated['enable_client_messages'] = $request->boolean('enable_client_messages', false);
        $validated['enable_user_authentication'] = $request->boolean('enable_user_authentication', false);

        // 获取客户端的 push app
        $pushApp = $client->pushApps()->first();

        if (!$pushApp) {
            // 如果不存在，则创建一个新的 push app
            $pushApp = $client->pushApps()->create(array_merge($validated, [
                'enabled' => false,
                'key' => Str::random(32),
                'secret' => Str::random(32),
            ]));
        } else {
            // 如果存在，则更新现有的 push app
            if ($pushApp->key === null || $pushApp->secret === null) {
                // 如果 key 为 null，则重置 push app
                $pushApp->update(array_merge($validated, [
                    'enabled' => false,
                    'key' => Str::random(32),
                    'secret' => Str::random(32),
                ]));
            } else {
                $pushApp->update($validated);
            }
        }

        $r = redirect()->route('clients.push-apps.index', $client->id);

        // 如果 enable 为 true，则返回应用 Secret
        if ($pushApp->enabled) {
            $r->with('secret', $pushApp->secret);
        }

        return $r;
    }

    public function delete(Client $client)
    {
        $this->authorize('managePush', $client);

        $client->pushApps()->firstOrFail()->delete();

        return redirect()->route('clients.show', $client)->with('info', '推送应用已删除');
    }
}
