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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 10, 4)->default(0)->change();
        });

        Schema::table('balances', function (Blueprint $table) {
            $table->decimal('amount', 10, 4)->default(0)->change();
            $table->decimal('remaining_amount', 10, 4)->default(0)->change();
        });

        Schema::table('unit_prices', function (Blueprint $table) {
            $table->decimal('price_per_unit', 10, 4)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('amount', 10, 4)->default('0')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 10)->default(0)->change();
        });

        Schema::table('balances', function (Blueprint $table) {
            $table->decimal('amount', 10)->default(0)->change();
            $table->decimal('remaining_amount', 10)->default(0)->change();
        });

        Schema::table('unit_prices', function (Blueprint $table) {
            $table->decimal('price_per_unit')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('amount')->default('0')->change();
        });
    }
};
