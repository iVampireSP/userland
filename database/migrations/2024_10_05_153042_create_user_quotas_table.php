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
        Schema::create('user_quotas', function (Blueprint $table) {
            $table->id();


            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quota_id')->constrained('quotas')->cascadeOnDelete();


            // 用户用量 (decimal)
            $table->decimal('amount');

            // 最大允许用量
            $table->decimal('max_amount');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quotas');
    }
};
