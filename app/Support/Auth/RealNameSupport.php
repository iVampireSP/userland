<?php

namespace App\Support\Auth;

use App\Exceptions\CommonException;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * 实名认证支持
 */
class RealNameSupport
{
    private string $url = 'https://ckidface.market.alicloudapi.com';

    private string $app_code;

    private PendingRequest $http;

    public function __construct()
    {
        $this->app_code = config('settings.supports.real_name.code');

        $this->http = Http::withHeaders([
            'Authorization' => 'APPCODE '.$this->app_code,
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
            'Accept' => 'application/json',
        ])->baseUrl($this->url);
    }

    /**
     * 创建实名认证请求
     *
     *
     * @throws CommonException|ConnectionException
     */
    public function create(User $user, $image_b64): string
    {
        $info = $user->getTempIdCard();

        if (empty($info['name'])) {
            throw new CommonException('获取用户信息的时候出现了问题。');
        }

        return $this->submit($info['name'], $info['id_card'], $image_b64);
    }

    /** 向 实名认证服务 发送请求
     *
     *
     * @throws CommonException|ConnectionException
     */
    public function submit(string $name, string $id_card, string $image_b64): bool
    {
        $data = [
            'idcard' => $id_card,
            'name' => $name,
            'image' => $image_b64,
            'liveck' => '1',
        ];

        $resp = $this->http->asForm()->post('/lundear/idface', $data);

        // 检测 status code
        if ($resp->status() !== 200) {
            throw new ConnectionException('远程服务器没有返回预期的状态码。');
        }

        $resp = $resp->json();

        if (! $resp) {
            throw new CommonException('调用远程服务器时出现了问题，请检查身份证号码是否正确。');
        }

        $code = $resp['code'];

        if ($code == 0) {
            return true;
        }

        throw new CommonException($resp['desc']);
    }

    public function getAge(string $id_card): int
    {
        return (new IDCardSupport)->getAge($id_card);
    }
}
