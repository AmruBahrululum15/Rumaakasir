<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('promotions',function(Blueprint $t) {
            $t->id();$t->foreignId('business_id')->constrained();$t->string('name');$t->string('code',50)->nullable();
            $t->string('discount_type',20);$t->decimal('discount_value',16,2);$t->decimal('min_transaction',16,2)->default(0);$t->decimal('max_discount',16,2)->nullable();
            $t->dateTime('start_at');$t->dateTime('end_at');$t->boolean('is_active')->default(true);
            $t->unsignedInteger('usage_limit')->nullable();$t->unsignedInteger('per_customer_limit')->nullable();$t->timestamps();
            $t->unique(['business_id','code']);
        });
        Schema::create('promotion_usages',function(Blueprint $t) {
            $t->id();$t->foreignId('business_id')->constrained();$t->foreignId('promotion_id')->constrained();
            $t->foreignId('sale_id')->constrained();$t->foreignId('customer_id')->constrained();$t->decimal('discount',16,2);$t->timestamp('created_at')->useCurrent();
            $t->unique('sale_id');$t->index(['business_id','promotion_id','customer_id']);
        });
        Schema::table('sales',fn(Blueprint $t)=>$t->decimal('promotion_discount',16,2)->default(0));
    }
    public function down(): void { /* Preserve financial records. */ }
};
