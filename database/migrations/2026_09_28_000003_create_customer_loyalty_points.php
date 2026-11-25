<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('customer_loyalty_points', function(Blueprint $t) {
            $t->id(); $t->foreignId('business_id')->constrained(); $t->foreignId('customer_id')->constrained();
            $t->bigInteger('points'); $t->string('source_type',30); $t->unsignedBigInteger('source_id');
            $t->string('description'); $t->timestamp('created_at')->useCurrent();
            $t->unique(['business_id','source_type','source_id'],'loyalty_source_unique');
            $t->index(['business_id','customer_id']);
        });
        Schema::table('sales',function(Blueprint $t) {
            $t->unsignedInteger('loyalty_redeem_points')->default(0);
            $t->decimal('loyalty_discount',16,2)->default(0);
        });
    }
    public function down(): void { /* Financial ledger is retained deliberately. */ }
};
