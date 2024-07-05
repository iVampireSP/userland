<?php

namespace App\Http\Controllers\Application;

use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use App\Support\FaceSupport;
use App\Support\MilvusSupport;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected FaceSupport $faceSupport;
    protected MilvusSupport $milvusSupport;
    public function __construct()
    {
        $this->faceSupport = new FaceSupport();
        $this->milvusSupport = new MilvusSupport();
    }

    // 创建 Face 注册
    public function createFaceRegister(Request $request)
    {
        $request->validate([
            'image_b64' => 'required|string',
        ]);

        $image_b64 = $request->input('image_b64');

        try {
            $this->faceSupport->check($image_b64);
        } catch (CommonException $e) {
            $this->badRequest($e->getMessage());
        }

        try {
            $embedding = $this->faceSupport->test_image($image_b64);
        } catch (CommonException $e) {
            $this->badRequest($e->getMessage());
        }

        // 检测是否存在
        try {
            $faces = $this->faceSupport->search($embedding);
        } catch (CommonException $e) {
            return $this->serverError($e->getMessage());
        }

        if (count($faces) > 1) {
            $this->conflict('该人脸已存在。');
        }

        // 创建快速登录链接



    }
}
