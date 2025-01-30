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
        Schema::create('face_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();  // 会话ID
            $table->foreignId('user_id')->nullable()->constrained();  // 关联用户
            $table->json('initial_face_data')->nullable();  // 初始人脸数据
            $table->json('action_sequence')->nullable();  // 动作序列
            $table->json('flash_sequence')->nullable();  // 炫光序列
            $table->json('verification_data')->nullable();  // 验证过程数据
            $table->enum('status', ['pending', 'processing', 'failed', 'completed'])->default('pending');
            $table->timestamp('expires_at');  // 过期时间
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_verifications');
    }
};
