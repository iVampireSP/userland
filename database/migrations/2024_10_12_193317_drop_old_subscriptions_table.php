<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::drop('subscription_renewals');
        Schema::drop('subscriptions');
        Schema::drop('feature_consumptions');
        Schema::drop('feature_plan');
        Schema::drop('feature_tickets');
        Schema::drop('features');
        Schema::drop('plans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
