<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {Schema::create('bookings',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('customer_id')->constrained();$t->foreignId('staff_id')->nullable()->constrained('users');$t->foreignId('service_id')->constrained('business_services');$t->string('service_name');$t->dateTime('starts_at');$t->dateTime('ends_at');$t->string('status',20)->default('pending');$t->text('notes')->nullable();$t->timestamps();$t->index(['business_id','staff_id','starts_at']);});}
    public function down():void { /* Preserve appointments. */ }
};
