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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('description')->nullable();

            $table->longText('content')->nullable();

            $table->string('name')->index()->unique();

            $table->foreignId('category_id')->nullable()->constrained('package_categories')->cascadeOnDelete();

            $table->string('currency')->default('CNY');

            //            $table->enum('period', ['forever', 'day', 'week', 'month', 'year'])->index()->default('month');
            //            $table->integer('period_count')->default(1);

            // enabled periods
            $table->boolean('enable_day')->default(false);
            $table->boolean('enable_week')->default(false);
            $table->boolean('enable_month')->default(false);
            $table->boolean('enable_year')->default(false);
            $table->boolean('enable_forever')->default(false);

            // price_forever, day, week, month, year
            $table->decimal('price_day')->default(0);
            $table->decimal('price_week')->default(0);
            $table->decimal('price_month')->default(0);
            $table->decimal('price_year')->default(0);
            $table->decimal('price_forever')->default(0);

            $table->boolean('hidden')->index()->default(false);

            // 下架
            $table->boolean('sold_out')->default(false);

            // 是否启用配额
            $table->boolean('enable_quota')->default(false);

            // 最大已激活数量
            $table->integer('max_active_count')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
