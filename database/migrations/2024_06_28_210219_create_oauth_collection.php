<?php

use App\Support\MilvusSupport;
use Illuminate\Database\Migrations\Migration;
use HelgeSverre\Milvus\Facades\Milvus;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private MilvusSupport $milvusSupport;

    public function __construct()
    {
        $this->milvusSupport = new MilvusSupport();
    }
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $resp = $this->milvusSupport->post('collections/create', [
            "collectionName" => config('milvus.collection'),
            "schema" => [
                "autoId" => true,
                "enabledDynamicField" => true,
                "fields" => [
                    [
                        "fieldName" => "id",
                        "dataType" => "Int64",
                        "isPrimary" => true,
                    ],
                    [
                        "fieldName" => "face_id",
                        "dataType" => "Int64",
                    ],
                    [
                        "fieldName" => "embedding",
                        "dataType" => "FloatVector",
                        "elementTypeParams" => ["dim" => (string)config('settings.supports.face.dimension')],
                    ],
                ],
            ],
            "indexParams" => [
                [
                    "fieldName" => "embedding",
                    "metricType" => "COSINE",
                    "indexName" => "embedding_idx",
                    "params" => ["index_type" => "HNSW", "nlist" => 1024],
                ],
                [
                    "fieldName" => "face_id",
                    "indexName" => "face_id_idx",
                    "params" => ["index_type" => "STL_SORT"],
                ],
            ],
        ]);

        if ($resp['code'] != 200) {
            throw new Exception($resp['message']);
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $resp = $this->milvusSupport->post('collections/drop', [
            "collectionName" => "oauth"
        ]);

        if ($resp['code'] != 200) {
            throw new Exception($resp['message']);
        }
    }
};
