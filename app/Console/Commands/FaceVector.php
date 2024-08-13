<?php

namespace App\Console\Commands;

use App\Exceptions\CommonException;
use App\Models\Face;
use App\Support\FaceSupport;
use App\Support\MilvusSupport;
use Illuminate\Console\Command;

class FaceVector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'face:vector {--user-id= : 用户 ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重新生成用户人脸特征向量';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $milvusSupport = new MilvusSupport();
        $faceSupport = new FaceSupport();

        $user_id = $this->option('user-id');

        // if empty
        if (empty($user_id)) {
            $this->warn('你没有提供用户 ID，是否需要重新生成所有用户的人脸特征向量？');

            if (! $this->confirm('确定吗？')) {
                return;
            }

            Face::query()->with('user')->chunk(100, function ($faces) use ($faceSupport) {
                $faces->each(function (Face $face) use ($faceSupport) {
                    $this->info("正在重新生成用户 {$face->user->name} 的人脸特征向量...");

                    $b64 = $face->getFile();

                    if (empty($b64)) {
                        $this->error("用户 {$face->user->name} 的人脸文件不存在，图片文件可能已经损坏，将删除。");

                        $face->delete();

                        return;
                    }

                    // 检测是不是正常图片
                    try {
                        $faceSupport->check($b64);
                    } catch (CommonException $e) {
                        $this->error($e->getMessage());

                        return;
                    }

                    try {
                        $new_embedding = $faceSupport->test_image($b64);
                    } catch (CommonException $e) {
                        $this->error($e->getMessage());

                        return;
                    }

                    if ($face->setEmbedding($new_embedding)) {
                        $this->info("重新生成用户 {$face->user->name} 的人脸特征向量成功。");
                    } else {
                        $this->error("重新生成用户 {$face->user->name} 的人脸特征向量失败。");
                    }
                });
            });
        }
    }
}
