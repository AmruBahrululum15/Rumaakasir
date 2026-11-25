<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('submission_token')->unique();
            foreach (['store_name', 'business_type', 'store_phone', 'store_email', 'province', 'city',
                'owner_name', 'owner_phone'] as $field) $table->string($field);
            foreach (['legal_name', 'business_type_other', 'district', 'postal_code', 'owner_position'] as $field) $table->string($field)->nullable();
            $table->text('address');
            $table->string('website', 500)->nullable();
            $table->string('social_media', 500)->nullable();
            $table->string('owner_username', 100)->unique();
            $table->string('owner_email')->unique();
            $table->string('requested_plan', 20);
            $table->string('billing_cycle', 20)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('business_id')->nullable()->unique();
            $table->unsignedBigInteger('owner_id')->nullable()->unique();
            $table->timestamp('activated_at')->nullable();
            $table->dateTime('terms_accepted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Registration history must be retained. No destructive rollback.
    }
};
