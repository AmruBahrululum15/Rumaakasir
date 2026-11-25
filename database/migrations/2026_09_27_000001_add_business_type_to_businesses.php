<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('businesses', 'business_type')) {
            Schema::table('businesses', fn (Blueprint $table) => $table->string('business_type', 50)->nullable());
        }
        // Existing tenants remain null (legacy), including business_id=1.
    }
    public function down(): void { /* Preserve existing business data. */ }
};
