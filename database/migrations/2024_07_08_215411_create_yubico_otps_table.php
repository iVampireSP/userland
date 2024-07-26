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
        Schema::create('yubico_otps', function (Blueprint $table) {
            $table->id();

            $table->string('device_id')->index();

            $table->string('model_type')->index();

            $table->unsignedBigInteger('model_id')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yubico_otps');
    }
};
