<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::create('outlets',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->string('name');$t->text('address')->nullable();$t->string('phone',30)->nullable();$t->boolean('is_active')->default(true);$t->timestamps();$t->index(['business_id','is_active']);});
        foreach(['users','sales','purchases'] as $table)Schema::table($table,fn(Blueprint $t)=>$t->foreignId('outlet_id')->nullable()->constrained());
    }
    public function down():void { /* Existing records and nullable references are retained. */ }
};
