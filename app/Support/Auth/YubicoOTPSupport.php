<?php


namespace App\Support\Auth;

use App\Contracts\YubicoOTP;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YubicoOTPSupport implements YubicoOTP
{
    // Thanks https://github.com/grizzlyware/yubikey-php
    private string $otp;

    private string $client_id;

    private string $client_secret;

    private array $servers;

    private int $tolerance = 15;

    private array $errors = [
        'BACKEND_ERROR',
        'BAD_OTP',
        'BAD_SIGNATURE',
        'MISSING_PARAMETER',
        'NO_SUCH_CLIENT',
        'NOT_ENOUGH_ANSWERS',
        'OPERATION_NOT_ALLOWED',
        'REPLAYED_OTP',
        'REPLAYED_REQUEST',
    ];

    /**
     * Create a new class instance.
     */
    public function __construct(array $servers, string $client_id, string $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = base64_decode($client_secret);
        $this->servers = $servers;
    }

    /**
     * @return self
     *
     * @throws Exception
     */
    public function setOTP(string $otp): YubicoOTP
    {
        $this->otp = $otp;

        return $this;
    }

    public function getDeviceID(): string
    {
        // 取前 12 位
        return substr($this->otp, 0, 12);
    }

    public function verify(): bool
    {
        if (config('app.debug')) {
            return true;
        }

        // 如果长度不是 44 位，则不验证
        if (strlen($this->otp) != 44) {
            return false;
        }

        try {
            $this->validateOTP();

            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return false;
        }
    }

    /**
     * @throws Exception
     */
    private function validateOTP(): void
    {
        $nonce = substr(md5(microtime()), 0, 16) . Str::random();
        $query = [
            'id' => $this->client_id,
            'otp' => $this->otp,
            'nonce' => $nonce,
        ];

        ksort($query);

        $url = $this->getVerificationUrl() . '?' . http_build_query($query) . '&h=' . urlencode($this->signPayload($query));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);

        if (!$response) {
            throw new Exception('cURL Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            throw new Exception('Non 200 response code received from Yubico verifiation server');
        }
        curl_close($ch);

        $formattedResponse = [];

        foreach (explode("\n", $response) as $responseLine) {
            $lineParts = explode('=', $responseLine, 2);
            if (count($lineParts) < 2) {
                continue;
            }

            $lineParts[0] = trim($lineParts[0]);
            $lineParts[1] = trim($lineParts[1]);

            if ($lineParts[0]) {
                $formattedResponse[$lineParts[0]] = $lineParts[1];
            }
        }

        // 检查 status 是否在 $errors 中
        if (in_array($formattedResponse['status'], $this->errors)) {
            Log::error('Invalid status response from Yubico, got ' . $formattedResponse['status']);
            throw new Exception('Invalid status response from Yubico, got ' . $formattedResponse['status']);
        }

        // Check the nonce and OTP
        if (!isset($formattedResponse['nonce'])) {
            throw new Exception('Response does not contain a nonce');
        }
        if (!isset($formattedResponse['otp'])) {
            throw new Exception('Response does not contain a OTP');
        }
        if ($query['nonce'] != $formattedResponse['nonce']) {
            throw new Exception('Responses nonce does not match requests');
        }
        if ($query['otp'] != $formattedResponse['otp']) {
            throw new Exception('Responses OTP does not match requests');
        }

        // Extract the response hash for verification?
        $responseToValidate = $formattedResponse;
        unset($responseToValidate['h']);
        $expectedHash = $this->signPayload($responseToValidate);
        if ($formattedResponse['h'] != $expectedHash) {
            throw new Exception('Invalid hash response from Yubico');
        }

        // Verify the time
        if (!isset($formattedResponse['t'])) {
            throw new Exception('Response timestamp not set');
        }
        $currentTimestamp = time();
        $responseTime = DateTime::createFromFormat('Y-m-d\TH:i:s+', $formattedResponse['t'], new DateTimeZone('UTC'));
        if ($responseTime->getTimestamp() > $currentTimestamp + $this->tolerance) {
            throw new Exception('Response timestamp is out of bounds (ahead)');
        }
        if ($responseTime->getTimestamp() < $currentTimestamp - $this->tolerance) {
            throw new Exception('Response timestamp is out of bounds (behind)');
        }

        if (!isset($formattedResponse['status'])) {
            throw new Exception('Response status not set');
        }

        if ($formattedResponse['status'] != 'OK') {
            throw new Exception('Response status is not OK');
        }
    }

    protected function signPayload($query): string
    {
        return base64_encode(hash_hmac('sha1', urldecode(http_build_query($this->orderQueryString($query))), $this->client_secret, true));
    }

    protected static function orderQueryString($query)
    {
        ksort($query);

        return $query;
    }

    protected function getVerificationUrl(): string
    {
        return $this->servers[array_rand($this->servers)];
    }
}
