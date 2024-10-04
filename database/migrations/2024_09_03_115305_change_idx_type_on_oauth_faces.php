<?php

use App\Support\Milvus\MilvusSupport;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private MilvusSupport $milvusSupport;

    public function __construct()
    {
        $this->milvusSupport = new MilvusSupport;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $resp = $this->milvusSupport->releaseCollection(config('milvus.collection'));
        if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
            throw new Exception($resp['message']);
        }

        $resp = $this->milvusSupport->post('indexes/drop', [
            'collectionName' => config('milvus.collection'),
            'indexName' => 'face_id_idx',
        ]);
        if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
            throw new Exception($resp['message']);
        }

        // 等待释放
        echo "Waiting for release...\n";
        while (true) {
            $resp = $this->milvusSupport->post('indexes/list', [
                'collectionName' => config('milvus.collection'),
            ]);
            if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
                throw new Exception($resp['message']);
            }

            // 如果 $resp['data'] 里面没有 face_id_idx，说明已经释放成功
            if (! in_array('face_id_idx', array_column($resp['data'], 'index_name'))) {
                break;
            }
            sleep(5);
        }

        $resp = $this->milvusSupport->post('indexes/create', [
            'collectionName' => config('milvus.collection'),
            'indexParams' => [
                (object) [
                    'fieldName' => 'face_id',
                    'indexName' => 'face_id_idx',
                    'metricType' => '',
                    'params' => [
                        'index_type' => 'INVERTED',
                    ],
                ],
            ],
        ]);
        if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
            throw new Exception($resp['message']);
        }

        $this->milvusSupport->loadCollection(config('milvus.collection'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $resp = $this->milvusSupport->post('indexes/drop', [
            'collectionName' => config('milvus.collection'),
            'indexName' => 'face_id_idx',
        ]);
        if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
            throw new Exception($resp['message']);
        }

        $resp = $this->milvusSupport->post('indexes/create', [
            'collectionName' => config('milvus.collection'),
            'indexParams' => [
                [
                    'fieldName' => 'face_id',
                    'indexName' => 'face_id_idx',
                    'metricType' => '',
                    'params' => [
                        'index_type' => 'STL_SORT',
                    ],
                ],
            ],
        ]);
        if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
            throw new Exception($resp['message']);
        }
    }
};
