<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_registrations', function (Blueprint $table) {
            foreach (['province_id', 'city_id', 'district_id', 'village_id'] as $field) $table->string($field, 20)->nullable();
            $table->string('village')->nullable();
            $table->boolean('address_verified')->default(false);
        });
    }

    public function down(): void { /* Keep registration history and address data. */ }
};
