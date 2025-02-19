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
        Schema::create('push_apps', function (Blueprint $table) {
            // 主键和基本信息
            $table->string('id')->primary();
            $table->string('key');
            $table->string('secret');

            // 连接和权限设置
            $table->integer('max_connections');
            $table->smallInteger('enable_client_messages');
            $table->smallInteger('enabled');

            // 速率限制
            $table->integer('max_backend_events_per_sec');
            $table->integer('max_client_events_per_sec');
            $table->integer('max_read_req_per_sec');

            // Presence 频道设置
            $table->integer('max_presence_members_per_channel')->nullable();
            $table->integer('max_presence_member_size_in_kb')->nullable();

            // 频道和事件限制
            $table->integer('max_channel_name_length')->nullable();
            $table->integer('max_event_channels_at_once')->nullable();
            $table->integer('max_event_name_length')->nullable();
            $table->integer('max_event_payload_in_kb')->nullable();
            $table->integer('max_event_batch_size')->nullable();

            // Webhook 和认证
            $table->json('webhooks')->nullable();
            $table->smallInteger('enable_user_authentication');

            // 时间戳
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
