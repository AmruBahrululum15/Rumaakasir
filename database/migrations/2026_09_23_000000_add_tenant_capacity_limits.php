<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedInteger('user_limit')->nullable();
            $table->unsignedInteger('product_limit')->nullable();
        });
    }

    public function down(): void
    {
        // Preserve existing tenant configuration; this upgrade is additive only.
    }
};
