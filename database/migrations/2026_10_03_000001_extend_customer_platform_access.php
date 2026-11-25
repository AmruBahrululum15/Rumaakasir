<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up():void
    {
        Schema::table('customer_accounts',function(Blueprint $table){
            // Nullable: no rewrite/backfill of existing accounts or contact details.
            $table->string('login_phone',20)->nullable()->index();
            $table->timestamp('last_login_at')->nullable();
        });
        Schema::create('customer_link_invitations',function(Blueprint $table){
            $table->id();$table->foreignId('business_id')->constrained();$table->foreignId('customer_id')->constrained();
            $table->foreignId('customer_account_id')->constrained();$table->foreignId('requested_by')->constrained('users');
            $table->string('email');$table->string('token_hash',64)->unique();
            $table->timestamp('expires_at');$table->timestamp('accepted_at')->nullable();$table->timestamps();
        });
    }
    public function down():void { /* Preserve accounts and invitations. */ }
};
