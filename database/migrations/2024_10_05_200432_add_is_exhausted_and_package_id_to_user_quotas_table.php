<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_quotas', function (Blueprint $table) {
            // package_id 外键
            $table->foreignId('package_id')->after('quota_id')->references('id')->on('packages')
                ->cascadeOnDelete();

            // 是否启用
            $table->boolean('enabled')->default(true)->after('package_id');

            // 是否用尽
            $table->boolean('is_exhausted')->default(false)->after('package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_quotas', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn('package_id');
            $table->dropColumn('enabled');
            $table->dropColumn('is_exhausted');
        });
    }
};
