<?php

namespace App\Console\Commands;

use App\Support\MilvusSupport;
use Exception;
use Illuminate\Console\Command;

class MilvusIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'milvus:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '查看 Milvus 索引信息。';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle()
    {
        $milvusSupport = new MilvusSupport;

        while (true) {
            // 清理屏幕
            echo "\033[H\033[2J";

            $resp = $milvusSupport->post('indexes/list', [
                'collectionName' => config('milvus.collection'),
            ]);
            if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
                throw new Exception($resp['message']);
            }

            $indexes = $resp['data'];

            foreach ($indexes as $index) {
                $info = $milvusSupport->post('indexes/describe', [
                    'collectionName' => config('milvus.collection'),
                    'indexName' => $index,
                ]);
                if ($info['code'] != MilvusSupport::CODE_SUCCESS) {
                    throw new Exception($info['message']);
                }

                $keys = [];
                $data = [];
                foreach ($info['data'] as $d) {
                    foreach ($d as $k => $v) {
                        // 检查
                        if (! in_array($k, $keys)) {
                            $keys[] = $k;
                        }

                        $data[$k] = $v;
                    }
                }

                $this->table($keys, [$data]);

            }
            sleep(3);
        }

    }
}
