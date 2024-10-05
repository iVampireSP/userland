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
        Schema::create('package_upgrades', function (Blueprint $table) {
            $table->id();

            // 原 package_id
            $table->foreignId('old_package_id')->constrained('packages')->cascadeOnDelete();

            // 新 package_id
            $table->foreignId('new_package_id')->constrained('packages')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_upgrades');
    }
};
