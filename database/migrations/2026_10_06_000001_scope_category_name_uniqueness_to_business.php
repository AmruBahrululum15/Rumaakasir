<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Include soft-deleted rows: the unique index covers all preserved data.
        $duplicate = DB::table('categories')->select('business_id', 'name')
            ->groupBy('business_id', 'name')->havingRaw('COUNT(*) > 1')->first();
        if ($duplicate) {
            throw new RuntimeException('Duplicate category names within a business. Migration stopped; review existing data without deleting or merging it.');
        }
        if (DB::table('categories')->whereNull('business_id')->exists()) {
            throw new RuntimeException('Categories without a business exist. Migration stopped; review ownership first.');
        }

        $indexes = Schema::getIndexes('categories');
        if (! collect($indexes)->contains(fn ($index) => $index['unique'] && $index['columns'] === ['business_id', 'name'])) {
            Schema::table('categories', fn (Blueprint $table) => $table->unique(['business_id', 'name']));
        }
        foreach ($indexes as $index) {
            if ($index['unique'] && ! $index['primary'] && $index['columns'] === ['name']) {
                Schema::table('categories', fn (Blueprint $table) => $table->dropUnique($index['name']));
            }
        }
    }

    public function down(): void
    {
        // Do not restore a global name constraint: valid cross-store names may exist.
        Schema::table('categories', fn (Blueprint $table) => $table->dropUnique('categories_business_id_name_unique'));
    }
};
