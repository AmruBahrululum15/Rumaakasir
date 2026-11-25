<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages');
            $table->string('business_type', 50);
            $table->json('settings');
            $table->timestamps();
            $table->unique(['package_id', 'business_type']);
        });
    }

    public function down(): void { /* Preserve configured package variants. */ }
};
