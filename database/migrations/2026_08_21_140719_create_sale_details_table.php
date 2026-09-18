<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the sale line-item table for new installations.
     *
     * Some existing databases contain this table from a migration that is no
     * longer present in the repository. The guard keeps those databases intact
     * when this corrective migration is first recorded.
     */
    public function up(): void
    {
        if (Schema::hasTable('sale_details')) {
            return;
        }

        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Intentionally non-destructive: this corrective migration may be first
     * recorded on an existing database where sale_details predates it.
     */
    public function down(): void
    {
        // No-op. Do not drop an existing transaction-detail table on rollback.
    }
};
