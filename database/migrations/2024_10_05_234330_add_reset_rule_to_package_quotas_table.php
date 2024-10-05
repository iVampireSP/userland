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
        Schema::table('package_quotas', function (Blueprint $table) {
            // add reset rule （日，周，月，半年，年）
            $table->enum('reset_rule', ['none', 'day', 'week', 'month', 'half_year', 'year'])->index()->after('max_amount');
        });

        Schema::table('user_quotas', function (Blueprint $table) {
            // last reset at
            $table->timestamp('last_reset_at')->nullable()->index()->after('max_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_quotas', function (Blueprint $table) {
            $table->dropColumn('reset_rule');
        });

        Schema::table('user_quotas', function (Blueprint $table) {
            $table->dropColumn('last_reset_at');
        });
    }
};
