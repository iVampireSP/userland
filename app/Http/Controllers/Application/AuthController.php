<?php

namespace App\Http\Controllers\Application;

use App\Exceptions\CommonException;
use App\Http\Controllers\Controller;
use App\Models\Face;
use App\Models\User;
use App\Support\FaceSupport;
use App\Support\MilvusSupport;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected FaceSupport $faceSupport;

    protected MilvusSupport $milvusSupport;

    protected string $face_reg_prefix = 'face:reg:';

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
            return $this->badRequest($e->getMessage());
        }

        // 检测是否存在
        try {
            $faces = $this->faceSupport->search($embedding);
        } catch (CommonException $e) {
            return $this->serverError($e->getMessage());
        }

        if (count($faces)) {
            return $this->conflict('该人脸已存在。');
        }

        // 创建用户
        $user = User::create([
            'name' => '未命名',
        ]);

        // 创建人脸
        $face = new Face;

        // decode
        $face = $face->createFace(Face::TYPE_VALIDATE, $user);

        $face->putFile($image_b64);

        // 存入 Milvus
        $milvusSupport = new MilvusSupport();

        try {
            $milvusSupport->insert([
                'face_id' => $face->id,
                'embedding' => $embedding,
            ]);

        } catch (ConnectionException) {
            try {
                $face->delete();
            } catch (Exception) {
                return $this->serverError('特征无法保存，且回滚更改时也发生错误。');
            }

            return $this->serverError('保存特征数据时发生了错误');
        }

        $token = Str::random(128);

        // 创建快速登录链接
        Cache::set($this->face_reg_prefix.$token, [
            'user_id' => $user->id,
        ], 60 * 60 * 24);

        return $this->success([
            'token' => $token,
            'user' => $user,
        ]);

    }
}
