<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('pro_until')->nullable();
            $table->string('stripe_customer_id', 64)->nullable()->index();
            $table->string('stripe_subscription_id', 64)->nullable();
        });

        // Stripe events already handled (Stripe can send the same one twice).
        Schema::create('stripe_events', function (Blueprint $table) {
            $table->string('id', 80)->primary();
            $table->string('type', 80);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_events');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['pro_until', 'stripe_customer_id', 'stripe_subscription_id']));
    }
};
