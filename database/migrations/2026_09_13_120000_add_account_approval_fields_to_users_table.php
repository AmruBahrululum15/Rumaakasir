<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Existing users remain able to sign in after this incremental change.
            $table->string('status', 20)->default('approved')->after('is_active');
            $table->string('requested_role', 100)->nullable()->after('status');
            $table->boolean('must_change_credentials')->default(false)->after('requested_role');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'requested_role', 'must_change_credentials']);
        });
    }
};
