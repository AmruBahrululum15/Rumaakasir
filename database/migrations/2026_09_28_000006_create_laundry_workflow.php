<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('business_services',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->string('name');$t->string('kind',20);$t->string('unit',20);$t->decimal('price',16,2);$t->unsignedInteger('duration_minutes')->nullable();$t->boolean('is_active')->default(true);$t->timestamps();});
        Schema::create('laundry_orders',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('customer_id')->constrained();$t->foreignId('service_id')->constrained('business_services');$t->string('service_name');$t->string('unit',20);$t->decimal('quantity',10,3);$t->decimal('price',16,2);$t->decimal('total',16,2);$t->decimal('paid',16,2)->default(0);$t->dateTime('estimated_at');$t->string('status',20)->default('received');$t->text('notes')->nullable();$t->timestamps();$t->index(['business_id','status']);});
        Schema::create('laundry_status_histories',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('laundry_order_id')->constrained();$t->foreignId('user_id')->constrained();$t->string('status',20);$t->timestamp('created_at')->useCurrent();});
        Schema::create('laundry_payments',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('laundry_order_id')->constrained();$t->foreignId('user_id')->constrained();$t->uuid('request_key');$t->decimal('amount',16,2);$t->string('method',20)->default('cash');$t->timestamp('created_at')->useCurrent();$t->unique(['business_id','request_key']);});
    }
    public function down(): void { /* Preserve operational records. */ }
};
