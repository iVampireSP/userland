<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use EasyWeChat\Kernel\Exceptions\BadRequestException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use EasyWeChat\OfficialAccount\Application as OfficialAccount;
use EasyWeChat\OfficialAccount\Message;
use EasyWeChat\OfficialAccount\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Throwable;

class WeChatController extends Controller
{
    protected OfficialAccount $officialAccount;

    protected Server $server;

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function __construct()
    {
        $this->officialAccount = app(OfficialAccount::class);
        $this->server = $this->officialAccount->getServer();
    }

    /**
     * Handle the incoming request.
     *
     * @return ResponseInterface
     *
     * @throws InvalidArgumentException
     * @throws BadRequestException
     * @throws RuntimeException
     * @throws Throwable
     */
    public function serve(Request $request)
    {
        Log::info('request arrived.');

        $this->server->addMessageListener('text', function (Message $message) {
            return $this->handleChatMessage($message);
        });

        $this->server->addEventListener('subscribe', function (Message $message) {
            return '感谢您关注 '.config('app.display_name').'，我们的地址是: '.url('/').'。';
        });

        return $this->server->serve();
    }

    private function handleChatMessage(Message $message): string
    {
        $content = $message['Content'];

        // 取第一个字符
        $firstChar = mb_substr($content, 0, 1);

        // 去除第一个字符
        $message['Content'] = mb_substr($content, 1);

        return match ($firstChar) {
            'b' => $this->wechatBind($message),
            default => $this->noSuchCommand(),
        };
    }

    private function noSuchCommand(): string
    {
        return '找不到对应的命令。';
    }

    private function wechatBind(Message $message): string
    {
        // openid
        $wechat_openid = $message['FromUserName'];

        $token = $message['Content'];

        $user = new User();
        $r = $user->getLoginToken(
            token: $token,
            prefix: 'wechat:bind'
        );

        if ($r) {
            $r->wechat_open_id = $wechat_openid;
            $r->save();

            return '绑定成功，你好 '.$r->name;
        }

        return '找不到对应的绑定请求。';
    }
}
