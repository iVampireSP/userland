<?php

namespace App\Support;

use App\Exceptions\CommonException;
use App\Models\Face;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FaceSupport
{
    /**
     * @throws ConnectionException
     */
    private function post(string $endpoint, string $image_b64): array
    {
        $url = config('settings.supports.face.api');
        $http = Http::baseUrl($url)->post($endpoint, [
            'image_b64' => $image_b64,
        ]);

        if ($http->failed()) {
            throw new ConnectionException($http->body());
        }

        return $http->json();
    }

    // 活体检测

    /**
     * @throws ConnectionException
     */
    public function liveness(string $image_b64): array
    {
        return $this->post('liveness', $image_b64);
    }

    /**
     * @throws ConnectionException
     */
    public function embedding(string $image_b64): array
    {
        return $this->post('embedding', $image_b64);
    }

    /**
     * @throws CommonException
     */
    public function check(string $image_b64): true
    {
        // 字符串大小不能超过 1mb
        if (strlen($image_b64) > 1024 * 1024) {
            throw new CommonException('图片大小不能超过 1mb。');
        }

        // 检测是不是 data:image/jpeg;base64
        if (! preg_match('/^data:image\/jpeg;base64,/', $image_b64)) {
            throw new CommonException('图片格式错误，需要为 jpeg。');
        }

        return true;
    }

    /**
     * @throws CommonException
     */
    public function test_image(string $image_b64): array
    {
        try {
            $liveness = $this->liveness($image_b64);

            if ($liveness['result']['label'] != 'RealFace') {
                throw new CommonException('活体检测失败，请重新尝试。');
            }
        } catch (ConnectionException $e) {
            throw new CommonException('验证活体时发生了错误。');
        }

        try {
            $embeddings = $this->embedding($image_b64);

            if ($embeddings['embeddings']['embedding']) {
                $embedding = $embeddings['embeddings']['embedding'];
            } else {
                throw new CommonException('提取特征时发现了错误，请再次尝试。');
            }

        } catch (ConnectionException $e) {
            throw new CommonException('提取特征时发现了错误，请再次尝试。');
        }

        return $embedding;
    }

    /**
     * @throws CommonException
     */
    public function search(array $embedding): Collection|false
    {
        $milvusSupport = new MilvusSupport();
        try {
            $results = $milvusSupport->search($embedding);
        } catch (ConnectionException $e) {
            throw new CommonException('搜索特征时发现了错误，请再次尝试。');
        }

        $face_ids = [];

        foreach ($results['data'] as $result) {

            if ($result['distance'] < 0.85) {
                continue;
            }
            $face_ids[] = $result['face_id'];
        }

        if (count($face_ids) == 0) {
            return false;
        }

        $faces = Face::whereIn('id', $face_ids)->with('user')->get();

        if (count($faces) == 0) {
            return false;
        }

        return $faces;
    }
}
