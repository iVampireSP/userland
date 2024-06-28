<?php

namespace App\Support;

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
            throw new ConnectionException();
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

}
