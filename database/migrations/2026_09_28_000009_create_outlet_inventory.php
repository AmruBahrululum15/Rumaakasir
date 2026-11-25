<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::create('outlet_stocks',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('outlet_id')->constrained();$t->foreignId('product_id')->constrained();$t->unsignedInteger('quantity')->default(0);$t->unique(['outlet_id','product_id']);});
        Schema::create('outlet_stock_transfers',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('from_outlet_id')->nullable()->constrained('outlets');$t->foreignId('to_outlet_id')->nullable()->constrained('outlets');$t->foreignId('product_id')->constrained();$t->unsignedInteger('quantity');$t->foreignId('user_id')->constrained();$t->uuid('request_key');$t->timestamp('created_at')->useCurrent();$t->unique(['business_id','request_key']);});
        Schema::create('outlet_stock_movements',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('outlet_id')->nullable()->constrained();$t->foreignId('product_id')->constrained();$t->integer('quantity');$t->string('source_type',30);$t->unsignedBigInteger('source_id');$t->timestamp('created_at')->useCurrent();$t->unique(['source_type','source_id','product_id','outlet_id'],'outlet_movement_source_unique');$t->index(['business_id','outlet_id','created_at']);});
    }
    public function down():void { /* Preserve inventory history. */ }
};
