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
        $resp = $this->milvusSupport->post('collections/create', [
            'collectionName' => config('milvus.collection'),
            'schema' => [
                'autoId' => true,
                'enabledDynamicField' => true,
                'fields' => [
                    [
                        'fieldName' => 'id',
                        'dataType' => 'Int64',
                        'isPrimary' => true,
                    ],
                    [
                        'fieldName' => 'face_id',
                        'dataType' => 'Int64',
                    ],
                    [
                        'fieldName' => 'embedding',
                        'dataType' => 'FloatVector',
                        'elementTypeParams' => ['dim' => (string) config('settings.supports.face.dimension')],
                    ],
                ],
            ],
            //            'indexParams' => [
            //                [
            //                    'fieldName' => 'embedding',
            //                    'metricType' => 'COSINE',
            //                    'indexName' => 'embedding_idx',
            //                    'params' => ['index_type' => 'HNSW', 'nlist' => 1024],
            //                ],
            //                [
            //                    'fieldName' => 'face_id',
            //                    'indexName' => 'face_id_idx',
            //                    'params' => ['index_type' => 'STL_SORT'],
            //                ],
            //            ],
        ]);
        if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
            throw new Exception($resp['message']);
        }

        $resp = $this->milvusSupport->post('indexes/create', [
            'collectionName' => config('milvus.collection'),
            'indexParams' => [
                [
                    'metricType' => 'COSINE',
                    'fieldName' => 'embedding',
                    'indexName' => 'embedding_idx',
                    //                    'params' => [
                    //                        'index_type' => 'AUTOINDEX',
                    //                        'nlist' => '1024',
                    //                    ],
                ],
                [
                    'fieldName' => 'face_id',
                    'indexName' => 'face_id_idx',
                    'index_type' => 'STL_SORT',
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $resp = $this->milvusSupport->post('collections/drop', [
            'collectionName' => config('milvus.collection'),
        ]);

        if ($resp['code'] != MilvusSupport::CODE_SUCCESS) {
            throw new Exception($resp['message']);
        }
    }
};
