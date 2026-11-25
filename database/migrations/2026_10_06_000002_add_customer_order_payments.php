<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('business_payment_settings',function(Blueprint $t){
            $t->id();$t->foreignId('business_id')->unique()->constrained();
            $t->boolean('cash_enabled')->default(true);$t->boolean('qris_enabled')->default(false);$t->boolean('va_enabled')->default(false);
            $t->string('qris_mode')->default('manual');$t->string('qris_path')->nullable();
            $t->text('midtrans_server_key')->nullable();$t->boolean('midtrans_production')->default(false);$t->json('va_banks')->nullable();$t->timestamps();
        });
        Schema::table('sales',fn(Blueprint $t)=>$t->string('order_status',20)->nullable()->index());
        Schema::create('customer_order_payments',function(Blueprint $t){
            $t->id();$t->foreignId('business_id')->constrained();$t->foreignId('sale_id')->unique()->constrained();
            $t->string('provider');$t->string('qris_path')->nullable();$t->text('server_key')->nullable();$t->boolean('production')->default(false);
            $t->json('enabled_payments')->nullable();$t->text('redirect_url')->nullable();$t->string('gateway_state')->default('new');
            $t->json('instructions')->nullable();$t->timestamps();
        });
    }
    public function down(): void { /* Preserve orders and merchant configuration. */ }
};
