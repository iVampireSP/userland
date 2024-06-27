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
        Schema::create('bans', function (Blueprint $table) {
            $table->id();

            $table->string('email')->index();

            $table->string('reason')->nullable();
            // banned by client_id

            //  识别码
            $table->string('code')->nullable()->index();

            $table->unsignedBigInteger('client_id')->index()->nullable();
            $table->foreign('client_id')->references('id')->on('oauth_clients')->onDelete('set null');

            $table->string('expires_at')->nullable()->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bans');
    }
};
