<?php

namespace App\Support;

use App\Contracts\SMS;
use App\Exceptions\SMS\SMSFailedException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SMSSupport implements SMS
{
    private string $templateId;
    private string $phone;
    private string $content;
    private array $variables;

//    https://www.guoyangyun.com/
    public const string VARIABLE_SEND_API = "https://api.guoyangyun.com/api/sms/smsmtm.htm";
    public const string SEND_API = "https://api.guoyangyun.com/api/sms/sendSmsApi.htm";

    /**
     * @param string $templateId
     * @return $this
     */
    public function setTemplateId(string $templateId): self
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * @param array $variables ['k' => 'v']
     * @return $this
     */
    public function setVariableContent(array $variables = []): self
    {
        $this->variables = $variables;

        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Response
     * @throws SMSFailedException
     * @throws RequestException
     */
    public function send(): Response
    {
        if (empty($this->phone)) {
            throw new SMSFailedException('手机号码不能为空');
        }

        if (empty($this->content)) {
            throw new SMSFailedException('短信内容不能为空');
        }

        try {
            $response = $this->http(self::SEND_API, [
                'mobile' => $this->phone,
                'content' => $this->content,
            ]);
            $response->throw();

            // 如果 code 是 0，则成功
            if ($response->json('code') !== 0) {
                throw new SMSFailedException($response->json('msg'));
            }

        } catch (ConnectionException $exception) {
            throw new SMSFailedException($exception->getMessage());
        }

        return $response;
    }

    /**
     * @throws SMSFailedException
     * @throws RequestException
     */
    public function sendVariable()
    {
        if (empty($this->phone)) {
            throw new SMSFailedException('手机号码不能为空');
        }

        if (empty($this->templateId)) {
            throw new SMSFailedException('短信模板不能为空');
        }

        if (empty($this->variables)) {
            throw new SMSFailedException('短信变量不能为空');
        }

        $var_data = [[]];
        $insert_data = &$var_data[0];
        // 往里面添加手机号码，确保是二维数组
        $insert_data['mobile'] = $this->phone;

        foreach ($this->variables as $key => $value) {
            $insert_data["**{$key}**"] = $value;
        }

        try {
            $response = $this->http(self::VARIABLE_SEND_API, [
                'content' => json_encode($var_data),
                'templateId' => $this->templateId,
            ]);
            $response->throw();
            // 如果 code 是 0，则成功
            if ($response->json('code') !== 0) {
                throw new SMSFailedException($response->json('msg'));
            }

        } catch (ConnectionException $exception) {
            throw new SMSFailedException($exception->getMessage());
        }

        return $response;
    }

    /**
     * @throws ConnectionException
     */
    private function http($url, array $data): Response
    {
        $data['smsSignId'] = config('settings.supports.sms.sign');
        $data['appkey'] = config('settings.supports.sms.app_key');
        $data['appsecret'] = config('settings.supports.sms.app_secret');

        // 以 form 方式发送
        return Http::asForm()->post($url, $data);
    }

}
