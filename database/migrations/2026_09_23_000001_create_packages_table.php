<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_tables')->nullable();
            $table->boolean('export_excel')->default(false);
            $table->boolean('export_pdf')->default(false);
            $table->boolean('custom_branding')->default(false);
            $table->boolean('ai_agent')->default(false);
            $table->boolean('qr_order')->default(true);
            $table->timestamps();
        });

        DB::table('packages')->insert([
            [
                'code' => 'free', 'name' => 'Free',
                'max_products' => 100, 'max_users' => 2, 'max_tables' => 5,
                'export_excel' => false, 'export_pdf' => false,
                'custom_branding' => false, 'ai_agent' => false, 'qr_order' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'code' => 'premium', 'name' => 'Premium',
                'max_products' => null, 'max_users' => null, 'max_tables' => null,
                'export_excel' => true, 'export_pdf' => true,
                'custom_branding' => true, 'ai_agent' => true, 'qr_order' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        // Package configuration must be retained; upgrades are additive only.
    }
};
