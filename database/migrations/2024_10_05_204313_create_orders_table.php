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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // 订单类型（订阅还是充值，还是套餐包）
            $table->enum('type', ['subscription', 'recharge', 'package', 'package_upgrade'])->index();

            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid')->index();

            // 支付相关
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();

            // 订单金额
            $table->decimal('amount')->default('0');

            $table->string('currency')->default('CNY');

            // 订单关联
            // package id
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();

            // 需要升级到的 package_id
            $table->foreignId('upgrade_to_package_id')->nullable()->constrained('packages')->nullOnDelete();


            // 过期时间
            $table->timestamp('expired_at')->nullable()->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
