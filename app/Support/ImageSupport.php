<?php

namespace App\Support;

use App\Exceptions\CommonException;
use Illuminate\Support\Facades\Log;

class ImageSupport
{
    /**
     * @throws CommonException
     */
    public function convertToJpeg(string $image_b64): string
    {
        if ($image_b64 == 'data:,') {
            throw new CommonException('请重新扫描');
        }

        $fp = fopen('data://'.$image_b64, 'r');
        if (! $fp) {
            throw new CommonException('图片解析失败。');
        }

        $meta = stream_get_meta_data($fp);

        if ($meta['mediatype'] != 'data:image/jpeg' && $meta['mediatype'] != 'data:image/jpg') {
            Log::debug('图片格式不是 jpeg，使用 gd 图片转换，输入格式是 '.$meta['mediatype']);
            $image = stream_get_contents($fp);

            // 使用 gd 图片转换
            $image = imagecreatefromstring($image);

            // 开启输出缓冲区
            ob_start();
            $result = imagejpeg($image);
            if (! $result) {
                throw new CommonException('图片解析失败。');
            }

            $image_data = ob_get_contents();
            ob_end_clean();

            $image_b64 = 'data:image/jpeg;base64,'.base64_encode($image_data);
        }

        return $image_b64;
    }

    public function base64ToJpeg(string $image_b64): string
    {
        $data = explode(',', $image_b64);

        return base64_decode($data[1]);
    }
}
