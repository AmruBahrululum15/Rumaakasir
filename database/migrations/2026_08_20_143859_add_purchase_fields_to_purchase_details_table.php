<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('purchase_details', function (Blueprint $table) {

            $table->foreignId('purchase_id')
                ->after('id')
                ->constrained('purchases')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->after('purchase_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->integer('quantity')
                ->after('product_id')
                ->default(1);

            $table->decimal('price', 15, 2)
                ->after('quantity')
                ->default(0);

            $table->decimal('subtotal', 15, 2)
                ->after('price')
                ->default(0);
        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::table('purchase_details', function (Blueprint $table) {

            $table->dropForeign(['purchase_id']);
            $table->dropForeign(['product_id']);

            $table->dropColumn([
                'purchase_id',
                'product_id',
                'quantity',
                'price',
                'subtotal',
            ]);
        });
    }
};