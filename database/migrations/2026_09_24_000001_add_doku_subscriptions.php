<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedBigInteger('price_monthly')->default(0);
            $table->unsignedBigInteger('price_yearly')->default(0);
            $table->boolean('is_active')->default(true);
        });
        DB::table('packages')->where('code', 'premium')->update(['price_monthly' => 49000, 'price_yearly' => 499000]);
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('subscription_source', 20)->nullable();
            $table->timestamp('subscription_started_at')->nullable();
        });
        Schema::create('subscription_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->foreignId('plan_id')->constrained('packages')->restrictOnDelete();
            $table->string('plan_name', 100);
            $table->string('business_name');
            $table->string('billing_cycle', 10);
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('IDR');
            $table->string('gateway', 10)->default('DOKU');
            $table->string('status', 15)->default('pending');
            $table->string('order_id', 30)->unique();
            $table->uuid('request_id')->unique();
            $table->string('doku_reference')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('last_payment_status', 30)->nullable();
            $table->timestamp('payment_due_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        // Financial history and existing configuration are deliberately preserved.
    }
};
