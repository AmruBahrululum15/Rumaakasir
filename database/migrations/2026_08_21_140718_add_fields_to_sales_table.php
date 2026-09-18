<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('code', 50)
                ->unique()
                ->after('id');

            $table->date('sale_date')
                ->after('code');

            $table->decimal('total', 15, 2)
                ->default(0)
                ->after('sale_date');

            $table->text('notes')
                ->nullable()
                ->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['code']);

            $table->dropColumn([
                'code',
                'sale_date',
                'total',
                'notes',
            ]);
        });
    }
};