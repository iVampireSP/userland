<?php

namespace App\Support\Face;

use App\Exceptions\CommonException;
use App\Models\Face;
use App\Support\Image\ImageSupport;
use App\Support\Milvus\MilvusSupport;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ValueError;

class FaceSupport
{
    /**
     * @throws ConnectionException
     */
    private function post(string $endpoint, array $data): array
    {
        $url = config('settings.supports.face.api');
        $http = Http::baseUrl($url)->post($endpoint, $data);

        if ($http->failed()) {
            throw new ConnectionException($http->body());
        }

        return $http->json();
    }

    /**
     * @throws ConnectionException
     */
    public function represent(string $image_b64): array
    {
        return $this->post('represent', [
            'model_name' => 'Facenet512',
            'img' => $image_b64,
        ]);
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

        return true;
    }

    /**
     * @throws CommonException
     */
    public function test_image(string $image_b64): array
    {
        try {
            $image_b64 = (new ImageSupport)->convertToJpeg($image_b64);
        } catch (ValueError $e) {
            Log::error($e->getMessage());
            throw new CommonException('无法解析图片，是否已经损坏？');
        }
        try {
            $represent = $this->represent($image_b64);

            if (count($represent['results']) > 1) {
                throw new CommonException('画面中有多个人脸，请换个地方尝试。');
            }

            if ($represent['results'][0]['face_confidence'] < 0.8) {
                throw new CommonException('人脸置信度太低，请重新尝试。');
            }

            $embedding = $represent['results'][0]['embedding'];

        } catch (ConnectionException $e) {
            Log::error($e->getMessage());
            throw new CommonException('验证活体时发生了错误。');
        }

        return $embedding;
    }

    /**
     * @throws CommonException
     */
    public function search(array $embedding): Collection
    {
        $milvusSupport = new MilvusSupport;
        try {
            $results = $milvusSupport->search($embedding);
        } catch (ConnectionException $e) {
            Log::error($e->getMessage());
            throw new CommonException('搜索特征时发现了错误，请再次尝试。');
        }

        $face_ids = [];

        foreach ($results['data'] as $result) {
            if ($result['distance'] < 0.80) {
                continue;
            }

            $face_ids[] = $result['face_id'];
        }

        if (count($face_ids) == 0) {
            return collect();
        }

        $faces = Face::whereIn('id', $face_ids)->with('user')->get();

        if (count($faces) == 0) {
            return collect();
        }

        return $faces;
    }
}
