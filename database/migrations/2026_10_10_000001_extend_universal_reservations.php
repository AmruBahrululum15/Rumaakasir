<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void {
        Schema::create('booking_resources',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('table_id')->nullable()->constrained('tables');$t->string('name',150);$t->string('kind',30)->default('facility');$t->unsignedInteger('capacity')->default(1);$t->boolean('is_active')->default(true);$t->timestamps();$t->unique(['business_id','table_id']);});
        Schema::create('booking_staff_schedules',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('staff_id')->constrained('users');$t->unsignedTinyInteger('day_of_week');$t->string('opens_at',5);$t->string('closes_at',5);$t->timestamps();$t->unique(['business_id','staff_id','day_of_week']);});
        Schema::table('business_services',function(Blueprint $t){$t->string('booking_category',100)->nullable();$t->unsignedInteger('booking_capacity')->default(1);$t->unsignedInteger('buffer_minutes')->default(0);});
        Schema::table('bookings',function(Blueprint $t){$t->string('code',40)->nullable()->unique();$t->string('public_token_hash',64)->nullable()->unique();$t->foreignId('resource_id')->nullable()->constrained('booking_resources');$t->unsignedInteger('capacity_units')->default(1);$t->dateTime('reserved_until')->nullable();$t->string('customer_name',120)->nullable();$t->string('customer_phone',30)->nullable();$t->string('customer_email',190)->nullable();$t->decimal('total',16,2)->default(0);$t->decimal('required_payment',16,2)->default(0);$t->decimal('paid',16,2)->default(0);$t->string('payment_mode',20)->default('free');$t->string('payment_status',20)->default('unpaid');$t->foreignId('sale_id')->nullable()->unique()->constrained('sales');$t->dateTime('checked_in_at')->nullable();$t->index(['business_id','resource_id','starts_at']);});
        Schema::create('booking_payments',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained();$t->foreignId('booking_id')->constrained();$t->foreignId('user_id')->constrained();$t->uuid('request_key')->unique();$t->decimal('amount',16,2);$t->string('method',20)->default('cash');$t->timestamps();});
    }
    public function down():void { /* Preserve reservation and payment records. */ }
};
