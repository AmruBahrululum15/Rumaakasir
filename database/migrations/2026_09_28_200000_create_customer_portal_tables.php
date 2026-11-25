<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_accounts', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('phone', 30);
            $t->string('password'); $t->string('status')->default('active');
            $t->timestamp('email_verified_at')->nullable(); $t->rememberToken(); $t->timestamps();
        });
        Schema::create('customer_password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary(); $t->string('token'); $t->timestamp('created_at')->nullable();
        });
        Schema::create('business_customers', function (Blueprint $t) {
            $t->id(); $t->foreignId('business_id')->constrained();
            $t->foreignId('customer_account_id')->constrained();
            $t->foreignId('customer_id')->constrained();
            $t->timestamp('joined_at'); $t->timestamp('last_transaction_at')->nullable();
            $t->string('status')->default('active');
            $t->unique(['business_id', 'customer_account_id']); $t->unique(['business_id', 'customer_id']);
        });
        Schema::create('conversations', function (Blueprint $t) {
            $t->id(); $t->foreignId('business_id')->constrained(); $t->foreignId('customer_account_id')->constrained();
            $t->string('status')->default('open'); $t->timestamp('last_message_at')->nullable(); $t->timestamps();
            $t->unique(['business_id', 'customer_account_id']);
        });
        Schema::create('messages', function (Blueprint $t) {
            $t->id(); $t->foreignId('conversation_id')->constrained(); $t->string('sender_type');
            $t->unsignedBigInteger('sender_id'); $t->text('message'); $t->timestamp('read_at')->nullable(); $t->timestamps();
            $t->index(['conversation_id', 'read_at']);
        });
        foreach (['bookings', 'sales'] as $table) Schema::table($table, function (Blueprint $t) {
            $t->foreignId('customer_account_id')->nullable()->constrained();
        });
        Schema::create('portal_checkouts', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->foreignId('customer_account_id')->constrained();
            $t->foreignId('business_id')->constrained(); $t->foreignId('sale_id')->constrained(); $t->timestamp('created_at');
        });
        foreach (['customer_chat.view', 'customer_chat.reply'] as $name) {
            $permission = \Spatie\Permission\Models\Permission::findOrCreate($name, 'web');
            foreach (['Atasan', 'Admin'] as $role) {
                \Spatie\Permission\Models\Role::where('name', $role)->where('guard_name', 'web')->first()?->givePermissionTo($permission);
            }
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void { /* Additive migration: retain customer data. */ }
};
