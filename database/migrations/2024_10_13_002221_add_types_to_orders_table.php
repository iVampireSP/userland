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
        Schema::table('orders', function (Blueprint $table) {
            // update enum
            $table->enum('type', ['subscription', 'recharge', 'package', 'package_upgrade', 'package_renewal'])->change();

            $table->enum('status', ['unpaid', 'paid', 'cancelled', 'completed'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('type', ['subscription', 'recharge', 'package', 'package_upgrade'])->change();
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->change();
        });
    }
};
