<?php

namespace App\Support\Milvus;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MilvusSupport
{
    private string $api_version = 'v2';

    public const int CODE_SUCCESS = 0;

    public const int CODE_SUCCESS2 = 200;

    private function http()
    {
        return Http::baseUrl('http://'.config('milvus.host').':'.config('milvus.port').'/'.$this->api_version.'/vectordb')->withOptions([
            'version' => '2',
        ]);
    }

    private function header(): array
    {
        $token = config('milvus.token');

        if (empty($token)) {
            $token = config('milvus.username').':'.config('milvus.password');
        }

        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function mergeDb(array $data): array
    {
        $db = config('milvus.dbname');

        //        return $data;

        // 管他什么情况，先合并 dbName
        return array_merge($data, ['dbName' => $db]);
    }

    /**
     * @throws ConnectionException
     */
    public function post(string $endpoint, array $data = []): array
    {
        $resp = $this->http()->withHeaders($this->header())->asJson()->post($endpoint, $this->mergeDb($data));

        $resp = $resp->json();

        if (isset($resp['code'])) {
            // 检测是不是 CODE_SUCCESS 或 CODE_SUCCESS2
            if ($resp['code'] != self::CODE_SUCCESS && $resp['code'] != self::CODE_SUCCESS2) {
                throw new ConnectionException($resp['message']);
            }
        }

        return $resp;
    }

    /**
     * @throws ConnectionException
     */
    public function get(string $endpoint, array $data = []): array
    {
        $resp = $this->http()->withHeaders($this->header())->get($endpoint, $this->mergeDb($data));

        if ($resp->status() >= 400) {
            throw new ConnectionException($resp->body());
        }

        $resp = $resp->json();

        if (isset($resp['code']) && $resp['code'] != self::CODE_SUCCESS) {
            throw new ConnectionException($resp['message']);
        }

        return $resp;
    }

    /**
     * @throws ConnectionException
     */
    public function loadCollection(string $collectionName): array
    {
        return $this->post('collections/load', [
            'collectionName' => $collectionName,
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function releaseCollection(string $collectionName): array
    {
        return $this->post('collections/release', [
            'collectionName' => $collectionName,
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function insert(array $data): array
    {
        return $this->post('entities/insert', [
            'data' => [$data],
            'collectionName' => config('milvus.collection'),
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function delete(string $filter): array
    {
        $r = $this->post('entities/delete', [
            'filter' => $filter,
            'collectionName' => config('milvus.collection'),
        ]);

        Log::info('Milvus 删除', [
            'response' => $r,
        ]);

        return $r;
    }

    /**
     * @throws ConnectionException
     */
    public function search(array $vector): array
    {
        return $this->post('entities/search', [
            'data' => [$vector],
            'annsField' => 'embedding',
            'limit' => 5,
            'collectionName' => config('milvus.collection'),
            'outputFields' => [
                'face_id',
            ],
        ]);
    }
}
