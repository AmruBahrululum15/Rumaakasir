<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sale_approvals',function(Blueprint $t) {
            $t->id();$t->foreignId('business_id')->constrained();$t->foreignId('sale_id')->constrained();$t->foreignId('requested_by')->constrained('users');$t->foreignId('approved_by')->nullable()->constrained('users');
            $t->string('type',15);$t->text('reason');$t->string('status',30)->default('pending_approval');$t->text('review_note')->nullable();$t->timestamp('reviewed_at')->nullable();$t->timestamps();$t->index(['business_id','status']);
        });
        Schema::create('business_audit_entries',function(Blueprint $t) {
            $t->id();$t->foreignId('business_id')->constrained();$t->foreignId('user_id')->constrained();$t->string('action');$t->string('entity');$t->unsignedBigInteger('entity_id');$t->json('metadata');$t->timestamp('created_at')->useCurrent();
        });
        $p=\Spatie\Permission\Models\Permission::findOrCreate('sales.approve-refunds','web');
        foreach(\Spatie\Permission\Models\Role::whereIn('name',['Atasan','Admin'])->get() as $role)$role->givePermissionTo($p);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function down(): void { /* Preserve audit trail. */ }
};
